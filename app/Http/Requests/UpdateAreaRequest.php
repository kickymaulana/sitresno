<?php

namespace App\Http\Requests;

use App\Models\Area;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('masters.update') ?? false;
    }

    public function rules(): array
    {
        $area = $this->route('area');

        return ['name' => ['required', 'string', 'max:255', Rule::unique('areas', 'name')->ignore($area instanceof Area ? $area->id : $area)]];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama area wajib diisi.',
            'name.unique' => 'Nama area sudah digunakan.',
        ];
    }
}
