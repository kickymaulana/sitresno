<?php

namespace App\Http\Requests;

use App\Models\ModelSize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModelSizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('masters.update') ?? false;
    }

    public function rules(): array
    {
        $modelSize = $this->route('model_size');
        $id = $modelSize instanceof ModelSize ? $modelSize->id : $modelSize;

        return [
            'model' => ['required', 'string', 'max:255', Rule::unique('model_sizes')->where(fn ($query) => $query->where('size', $this->input('size')))->ignore($id)],
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
