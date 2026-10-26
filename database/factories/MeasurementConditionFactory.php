<?php

namespace Database\Factories;

use App\Models\MeasurementCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeasurementCondition>
 */
class MeasurementConditionFactory extends Factory
{
    protected $model = MeasurementCondition::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(2),
            'label' => fake()->words(2, true),
            'sort_order' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
