<?php

namespace Database\Factories;

use App\Models\MasterMould;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MasterMould> */
class MasterMouldFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('MLD-####'),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
