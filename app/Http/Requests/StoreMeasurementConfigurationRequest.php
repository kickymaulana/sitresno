<?php

namespace App\Http\Requests;

use App\Models\MeasurementConfiguration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMeasurementConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('masters.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'customerId' => ['required', 'integer', 'exists:customers,id'],
            'modelSizeId' => [
                'required',
                'integer',
                'exists:model_sizes,id',
                Rule::unique('measurement_configurations', 'model_size_id')
                    ->where('customer_id', $this->input('customerId'))
                    ->ignore($this->configurationToIgnore()),
            ],
            'isActive' => ['required', 'boolean'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.parameterName' => ['required', 'string', 'max:255'],
            'fields.*.condition' => ['required', 'string', Rule::exists('measurement_conditions', 'code')->where('is_active', true)],
            'fields.*.expectedValue' => ['nullable', 'string', 'max:255'],
            'fields.*.description' => ['nullable', 'string'],
            'fields.*.sortOrder' => ['required', 'integer', 'min:1'],
            'fields.*.isActive' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['fields', 'fields.*.parameterName', 'fields.*.condition'])) {
                return;
            }

            foreach ($this->input('fields', []) as $index => $field) {
                if (blank($field['parameterName'] ?? null)) {
                    $validator->errors()->add("fields.{$index}.parameterName", 'Nama parameter wajib diisi.');
                }
            }

            if ($validator->errors()->has('fields.*.parameterName')) {
                return;
            }

            $fields = collect($this->input('fields', []));
            $fieldsByCombination = $fields->groupBy(fn (array $field): string => mb_strtolower(trim((string) ($field['parameterName'] ?? ''))).'|'.$field['condition']);
            $duplicateGroups = $fieldsByCombination->filter(fn ($group): bool => $group->count() > 1);

            if ($duplicateGroups->isEmpty()) {
                return;
            }

            $validator->errors()->add('fields', 'Kombinasi parameter dan tahap tidak boleh duplikat dalam satu konfigurasi. Periksa field yang ditandai.');

            foreach ($duplicateGroups as $duplicateGroup) {
                $duplicateIndexes = $fields
                    ->keys()
                    ->filter(fn (int $index): bool => $duplicateGroup->contains($fields->get($index)))
                    ->values();

                foreach ($duplicateIndexes as $index) {
                    $otherPositions = $duplicateIndexes
                        ->reject(fn (int $duplicateIndex): bool => $duplicateIndex === $index)
                        ->map(fn (int $duplicateIndex): int => $duplicateIndex + 1)
                        ->join(', ');
                    $field = $fields->get($index);
                    $condition = str((string) $field['condition'])->replace('_', ' ')->title();
                    $parameterName = trim((string) $field['parameterName']);
                    $message = "Duplikat dengan Field {$otherPositions}: {$parameterName} pada tahap {$condition}.";

                    $validator->errors()->add("fields.{$index}.parameterName", $message);
                    $validator->errors()->add("fields.{$index}.condition", $message);
                }
            }
        }];
    }

    public function messages(): array
    {
        return [
            'customerId.required' => 'Customer wajib dipilih.',
            'customerId.exists' => 'Customer yang dipilih tidak valid.',
            'modelSizeId.required' => 'Model/Size wajib dipilih.',
            'modelSizeId.exists' => 'Model/Size yang dipilih tidak valid.',
            'modelSizeId.unique' => 'Konfigurasi untuk kombinasi Customer dan Model/Size ini sudah ada.',
            'isActive.required' => 'Status konfigurasi wajib ditentukan.',
            'fields.required' => 'Minimal satu field pengukuran wajib ditambahkan.',
            'fields.min' => 'Minimal satu field pengukuran wajib ditambahkan.',
            'fields.*.parameterName.required' => 'Nama parameter wajib diisi.',
            'fields.*.condition.required' => 'Tahap wajib dipilih.',
            'fields.*.condition.in' => 'Tahap yang dipilih tidak valid.',
            'fields.*.expectedValue.string' => 'Nilai harapan harus berupa teks yang valid.',
            'fields.*.expectedValue.max' => 'Nilai harapan maksimal 255 karakter.',
            'fields.*.sortOrder.required' => 'Urutan wajib diisi.',
            'fields.*.sortOrder.integer' => 'Urutan harus berupa bilangan bulat.',
            'fields.*.sortOrder.min' => 'Urutan minimal 1.',
        ];
    }

    protected function configurationToIgnore(): ?MeasurementConfiguration
    {
        return null;
    }
}
