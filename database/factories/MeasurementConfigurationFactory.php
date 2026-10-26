<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\MeasurementConfiguration;
use App\Models\ModelSize;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MeasurementConfiguration> */
class MeasurementConfigurationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'model_size_id' => ModelSize::factory(),
            'is_active' => true,
        ];
    }
}
