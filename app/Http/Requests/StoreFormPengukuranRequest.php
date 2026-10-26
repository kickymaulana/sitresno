<?php

namespace App\Http\Requests;

use App\Models\MeasurementConfiguration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreFormPengukuranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('records.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'areaId' => ['required', 'integer', 'exists:areas,id'],
            'masterMouldId' => ['required', 'integer', 'exists:master_moulds,id'],
            'customerId' => ['required', 'integer', 'exists:customers,id'],
            'hariKe' => ['nullable', 'string', 'max:255'],
            'mesin' => ['nullable', 'string', 'max:255'],
            'tanggalOutputSample' => ['nullable', 'date'],
            'tanggalPengukuran' => ['required', 'date', Rule::unique('form_pengukurans', 'tanggal_pengukuran')->where('customer_id', $this->input('customerId'))],
            'tanggalNaikMould' => ['nullable', 'date'],
            'dibuatOleh' => ['nullable', 'string', 'max:255'],
            'diperiksaOleh' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'menunggu_pemeriksaan', 'disetujui', 'ditolak'])],
            'items' => ['required', 'array', 'size:1'],
            'items.*.modelSizeId' => ['required', 'integer', 'exists:model_sizes,id'],
            'items.*.remarks' => ['nullable', 'string'],
            'items.*.actuals' => ['required', 'array', 'min:1'],
            'items.*.actuals.*.configurationFieldId' => ['required', 'integer', 'exists:measurement_configuration_fields,id'],
            'items.*.actuals.*.actual' => ['nullable', 'numeric'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['customerId', 'items', 'items.*.modelSizeId', 'items.*.actuals', 'items.*.actuals.*.configurationFieldId'])) {
                return;
            }

            foreach ($this->input('items') as $itemIndex => $item) {
                $configuration = MeasurementConfiguration::query()
                    ->where('customer_id', $this->integer('customerId'))
                    ->where('model_size_id', $item['modelSizeId'])
                    ->where('is_active', true)
                    ->with(['fields' => fn ($query) => $query->where('is_active', true)])
                    ->first();

                if ($configuration === null) {
                    $validator->errors()->add("items.$itemIndex.modelSizeId", 'Konfigurasi pengukuran untuk Customer dan Model/Size ini belum tersedia.');

                    continue;
                }

                $allowed = $configuration->fields->pluck('id')->sort()->values();
                $submitted = collect($item['actuals'])->pluck('configurationFieldId')->sort()->values();

                if ($submitted->duplicates()->isNotEmpty() || $submitted->all() !== $allowed->all()) {
                    $validator->errors()->add("items.$itemIndex.actuals", 'Field actual tidak sesuai dengan konfigurasi Customer dan Model/Size.');
                }
            }
        }];
    }

    public function messages(): array
    {
        return [
            'areaId.required' => 'Area wajib dipilih.',
            'masterMouldId.required' => 'Master mould wajib dipilih.',
            'customerId.required' => 'Customer wajib dipilih.',
            'tanggalPengukuran.required' => 'Tanggal pengukuran wajib diisi.',
            'tanggalPengukuran.unique' => 'Customer ini sudah memiliki form pada tanggal tersebut.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'items.required' => 'Model/Size wajib dipilih.',
            'items.size' => 'Satu Model/Size wajib dipilih untuk satu form pengukuran.',
            'items.*.modelSizeId.required' => 'Model/Size wajib dipilih.',
            'items.*.actuals.required' => 'Daftar pengukuran wajib diisi.',
            'items.*.actuals.min' => 'Konfigurasi harus memiliki minimal satu field aktif.',
            'items.*.actuals.*.configurationFieldId.required' => 'Identitas field konfigurasi wajib diisi.',
            'items.*.actuals.*.configurationFieldId.exists' => 'Field konfigurasi tidak valid.',
            'items.*.actuals.*.actual.numeric' => 'Nilai aktual harus berupa angka.',
        ];
    }
}
