<?php

namespace Database\Factories;

use App\Models\MeasurementCondition;
use App\Models\MeasurementConfiguration;
use App\Models\MeasurementConfigurationField;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MeasurementConfigurationField> */
class MeasurementConfigurationFieldFactory extends Factory
{
    public function definition(): array
    {
        return [
            'measurement_configuration_id' => MeasurementConfiguration::factory(),
            'parameter_name' => fake()->word(),
            'condition' => MeasurementCondition::query()->inRandomOrder()->value('code') ?? 'sebelum_cuci',
            'expected_value' => fake()->randomFloat(3, 0, 100),
            'description' => fake()->optional()->sentence(),
            'sort_order' => 1,
            'is_active' => true,
        ];
    }
}
