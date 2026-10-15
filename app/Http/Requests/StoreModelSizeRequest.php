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
            'model' => ['required', 'string', 'max:255', Rule::unique('model_sizes')->where(fn ($query) => $query->where('size', $this->input('size')))],
            'size' => ['required', 'string', 'max:255'],
            'fullName' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'model.required' => 'Model wajib diisi.',
            'model.unique' => 'Kombinasi model dan ukuran sudah digunakan.',
            'size.required' => 'Ukuran wajib diisi.',
            'fullName.required' => 'Nama lengkap wajib diisi.',
        ];
    }
}
