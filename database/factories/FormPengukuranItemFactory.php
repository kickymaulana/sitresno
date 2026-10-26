<?php

namespace Database\Factories;

use App\Models\FormPengukuran;
use App\Models\FormPengukuranItem;
use App\Models\ModelSize;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FormPengukuranItem> */
class FormPengukuranItemFactory extends Factory
{
    public function definition(): array
    {
        $modelSize = ModelSize::factory()->create();

        return [
            'form_pengukuran_id' => FormPengukuran::factory(),
            'model_size_id' => $modelSize,
            'model_size_name' => $modelSize->displayName(),
            'remarks' => fake()->optional()->sentence(),
            'sort_order' => 1,
        ];
    }
}
