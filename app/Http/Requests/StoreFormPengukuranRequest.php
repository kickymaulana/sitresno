<?php

namespace App\Http\Requests;

use App\Models\FormPengukuranHasil;
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
        $rules = [
            'areaId' => ['required', 'integer', 'exists:areas,id'],
            'masterMouldId' => ['required', 'integer', 'exists:master_moulds,id'],
            'modelSizeId' => ['required', 'integer', 'exists:model_sizes,id'],
            'customerId' => ['required', 'integer', 'exists:customers,id'],
            'hariKe' => ['nullable', 'string', 'max:255'],
            'mesin' => ['nullable', 'string', 'max:255'],
            'tanggalOutputSample' => ['nullable', 'date'],
            'tanggalPengukuran' => ['nullable', 'date'],
            'tanggalNaikMould' => ['nullable', 'date'],
            'dibuatOleh' => ['nullable', 'string', 'max:255'],
            'diperiksaOleh' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'menunggu_pemeriksaan', 'disetujui', 'ditolak'])],
            'results' => ['required', 'array', 'size:3'],
            'results.*.condition' => ['required', 'string', 'distinct', Rule::in(FormPengukuranHasil::CONDITIONS)],
            'results.*.remarks' => ['nullable', 'string'],
        ];

        foreach (FormPengukuranHasil::MEASUREMENTS as $measurement) {
            $rules["results.*.$measurement"] = ['nullable', 'numeric'];
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['results', 'results.*.condition'])) {
                return;
            }

            $conditions = collect($this->input('results', []))->pluck('condition')->sort()->values()->all();
            $requiredConditions = collect(FormPengukuranHasil::CONDITIONS)->sort()->values()->all();

            if ($conditions !== $requiredConditions) {
                $validator->errors()->add('results', 'Hasil harus memuat tepat tiga kondisi: sebelum cuci, sesudah cuci, dan setelah bakar.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'areaId.required' => 'Area wajib dipilih.',
            'areaId.exists' => 'Area yang dipilih tidak valid.',
            'masterMouldId.required' => 'Master mould wajib dipilih.',
            'masterMouldId.exists' => 'Master mould yang dipilih tidak valid.',
            'modelSizeId.required' => 'Ukuran model wajib dipilih.',
            'modelSizeId.exists' => 'Ukuran model yang dipilih tidak valid.',
            'customerId.required' => 'Pelanggan wajib dipilih.',
            'customerId.exists' => 'Pelanggan yang dipilih tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'results.required' => 'Tiga hasil pengukuran wajib diisi.',
            'results.size' => 'Hasil pengukuran harus berjumlah tiga kondisi.',
            'results.*.condition.required' => 'Kondisi hasil wajib diisi.',
            'results.*.condition.distinct' => 'Setiap kondisi hasil harus unik.',
            'results.*.condition.in' => 'Kondisi hasil tidak valid.',
            'results.*.*.numeric' => 'Nilai pengukuran harus berupa angka.',
        ];
    }
}
