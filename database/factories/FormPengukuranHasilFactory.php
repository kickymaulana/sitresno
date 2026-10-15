<?php

namespace Database\Factories;

use App\Models\FormPengukuran;
use App\Models\FormPengukuranHasil;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FormPengukuranHasil> */
class FormPengukuranHasilFactory extends Factory
{
    public function definition(): array
    {
        return [
            'form_pengukuran_id' => FormPengukuran::factory(),
            'kondisi' => fake()->randomElement(FormPengukuranHasil::CONDITIONS),
            'remarks' => fake()->optional()->sentence(),
            'h' => fake()->randomFloat(3, 0, 999),
            'l' => fake()->randomFloat(3, 0, 999),
        ];
    }

    public function forCondition(string $condition): static
    {
        return $this->state(fn (): array => ['kondisi' => $condition]);
    }
}
