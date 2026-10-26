<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreModelSizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('masters.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('model_sizes', 'name')],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Model/Size wajib diisi.',
            'name.unique' => 'Model/Size sudah digunakan.',
        ];
    }
}
