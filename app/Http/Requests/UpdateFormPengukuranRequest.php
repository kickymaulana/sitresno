<?php

namespace App\Http\Requests;

use App\Models\FormPengukuran;
use App\Models\MeasurementConfiguration;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateFormPengukuranRequest extends StoreFormPengukuranRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('records.update') ?? false;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $formPengukuran = $this->formPengukuran();

        $rules['tanggalPengukuran'] = [
            'required',
            'date',
            Rule::unique('form_pengukurans', 'tanggal_pengukuran')
                ->where('customer_id', $this->input('customerId'))
                ->ignore($formPengukuran),
        ];

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['customerId', 'items', 'items.*.modelSizeId', 'items.*.actuals', 'items.*.actuals.*.configurationFieldId'])) {
                return;
            }

            $formPengukuran = $this->formPengukuran();
            $formPengukuran?->load('items.actuals');

            foreach ($this->input('items') as $itemIndex => $item) {
                $existingItem = $formPengukuran?->items->firstWhere('model_size_id', (int) $item['modelSizeId']);
                $keepsOriginalSnapshot = $formPengukuran?->status !== 'draft'
                    && $formPengukuran?->customer_id === $this->integer('customerId')
                    && $existingItem?->measurement_configuration_id !== null;

                if ($keepsOriginalSnapshot) {
                    $allowed = $existingItem->actuals->pluck('measurement_configuration_field_id')->filter()->sort()->values();
                } else {
                    $configuration = MeasurementConfiguration::query()
                        ->where('customer_id', $this->integer('customerId'))
                        ->where('model_size_id', $item['modelSizeId'])
                        ->where('is_active', true)
                        ->with(['fields' => fn ($query) => $query->where('is_active', true)])
                        ->first();

                    if ($configuration === null) {
                        $validator->errors()->add("items.$itemIndex.modelSizeId", 'Konfigurasi pengukuran untuk Customer dan Model/Size ini belum tersedia.');

                        continue;
                    }

                    $allowed = $configuration->fields->pluck('id')->sort()->values();
                }

                $submitted = collect($item['actuals'])->pluck('configurationFieldId')->sort()->values();

                if ($submitted->duplicates()->isNotEmpty() || $submitted->all() !== $allowed->all()) {
                    $validator->errors()->add("items.$itemIndex.actuals", 'Field actual tidak sesuai dengan snapshot konfigurasi form.');
                }
            }
        }];
    }

    private function formPengukuran(): ?FormPengukuran
    {
        $formPengukuran = $this->route('form_pengukuran');

        return $formPengukuran instanceof FormPengukuran ? $formPengukuran : null;
    }
}
