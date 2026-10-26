<?php

namespace App\Http\Requests;

use App\Models\MeasurementConfiguration;
use Illuminate\Validation\Validator;

class UpdateMeasurementConfigurationRequest extends StoreMeasurementConfigurationRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('masters.update') ?? false;
    }

    public function after(): array
    {
        return [
            ...parent::after(),
            function (Validator $validator): void {
                $configuration = $this->configurationToIgnore();

                if ($configuration === null || ! $configuration->formPengukuranItems()->exists()) {
                    return;
                }

                if ($configuration->customer_id !== $this->integer('customerId')) {
                    $validator->errors()->add('customerId', 'Customer tidak dapat diubah karena konfigurasi sudah digunakan pada form pengukuran.');
                }

                if ($configuration->model_size_id !== $this->integer('modelSizeId')) {
                    $validator->errors()->add('modelSizeId', 'Model/Size tidak dapat diubah karena konfigurasi sudah digunakan pada form pengukuran.');
                }
            },
        ];
    }

    protected function configurationToIgnore(): ?MeasurementConfiguration
    {
        $configuration = $this->route('measurement_configuration');

        return $configuration instanceof MeasurementConfiguration ? $configuration : null;
    }
}
