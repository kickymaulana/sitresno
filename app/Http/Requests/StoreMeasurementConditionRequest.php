<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMeasurementConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('masters.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('measurement_conditions', 'code')],
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode tahap wajib diisi.',
            'code.unique' => 'Kode tahap sudah digunakan.',
            'code.alpha_dash' => 'Kode tahap hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'label.required' => 'Nama tahap wajib diisi.',
            'sort_order.required' => 'Urutan wajib diisi.',
            'sort_order.min' => 'Urutan minimal 1.',
        ];
    }
}
