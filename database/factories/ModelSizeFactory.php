<?php

namespace Database\Factories;

use App\Models\ModelSize;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ModelSize> */
class ModelSizeFactory extends Factory
{
    public function definition(): array
    {
        $model = fake()->unique()->bothify('MDL-###');
        $size = fake()->randomElement(['XS', 'S', 'M', 'L', 'XL']);

        return [
            'model' => $model,
            'size' => $size,
            'full_name' => "$model $size",
            'description' => fake()->optional()->sentence(),
        ];
    }
}
