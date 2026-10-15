<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('masters.create') ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255', 'unique:areas,name']];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama area wajib diisi.',
            'name.unique' => 'Nama area sudah digunakan.',
        ];
    }
}
