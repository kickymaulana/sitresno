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
            'name' => ['required', 'string', 'max:255', Rule::unique('model_sizes', 'name')->ignore($id)],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return (new StoreModelSizeRequest)->messages();
    }
}
