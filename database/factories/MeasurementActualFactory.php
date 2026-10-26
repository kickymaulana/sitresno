<?php

namespace Database\Factories;

use App\Models\FormPengukuranItem;
use App\Models\MeasurementActual;
use App\Models\MeasurementCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MeasurementActual> */
class MeasurementActualFactory extends Factory
{
    public function definition(): array
    {
        return [
            'form_pengukuran_item_id' => FormPengukuranItem::factory(),
            'condition' => MeasurementCondition::query()->inRandomOrder()->value('code') ?? 'sebelum_cuci',
            'specification' => fake()->randomFloat(3, 1, 500),
            'actual' => fake()->randomFloat(3, 1, 500),
            'parameter_label' => fake()->word(),
            'parameter_description' => fake()->optional()->sentence(),
            'parameter_sort_order' => 1,
            'parameter_is_starred' => false,
        ];
    }
}
