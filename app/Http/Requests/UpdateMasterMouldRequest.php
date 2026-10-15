<?php

namespace App\Http\Requests;

use App\Models\MasterMould;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMasterMouldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('masters.update') ?? false;
    }

    public function rules(): array
    {
        $masterMould = $this->route('master_mould');

        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('master_moulds', 'code')->ignore($masterMould instanceof MasterMould ? $masterMould->id : $masterMould)],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode mould wajib diisi.',
            'code.unique' => 'Kode mould sudah digunakan.',
        ];
    }
}
