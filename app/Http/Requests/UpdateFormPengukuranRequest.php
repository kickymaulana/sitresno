<?php

namespace App\Http\Requests;

use App\Models\FormPengukuran;
use App\Models\FormPengukuranHasil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateFormPengukuranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('records.update') ?? false;
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
            'results.*.id' => ['nullable', 'integer', 'distinct', 'exists:form_pengukuran_hasils,id'],
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
            if (! $validator->errors()->hasAny(['results', 'results.*.condition'])) {
                $conditions = collect($this->input('results', []))->pluck('condition')->sort()->values()->all();
                $requiredConditions = collect(FormPengukuranHasil::CONDITIONS)->sort()->values()->all();

                if ($conditions !== $requiredConditions) {
                    $validator->errors()->add('results', 'Hasil harus memuat tepat tiga kondisi: sebelum cuci, sesudah cuci, dan setelah bakar.');
                }
            }

            if ($validator->errors()->has('results.*.id')) {
                return;
            }

            $formPengukuran = $this->route('form_pengukuran');
            $formId = $formPengukuran instanceof FormPengukuran ? $formPengukuran->id : (int) $formPengukuran;
            $ids = collect($this->input('results', []))->pluck('id')->filter()->values();

            if ($ids->isNotEmpty() && FormPengukuranHasil::query()->whereIn('id', $ids)->where('form_pengukuran_id', '!=', $formId)->exists()) {
                $validator->errors()->add('results', 'Hasil pengukuran tidak terdaftar pada form ini.');
            }
        }];
    }

    public function messages(): array
    {
        return (new StoreFormPengukuranRequest)->messages() + [
            'results.*.id.exists' => 'Hasil pengukuran tidak ditemukan.',
            'results.*.id.distinct' => 'ID hasil pengukuran tidak boleh duplikat.',
        ];
    }
}
