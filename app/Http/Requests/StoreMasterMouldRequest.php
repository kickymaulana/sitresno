<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMasterMouldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('masters.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', 'unique:master_moulds,code'],
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
