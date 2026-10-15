<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Customer;
use App\Models\FormPengukuran;
use App\Models\MasterMould;
use App\Models\ModelSize;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FormPengukuran> */
class FormPengukuranFactory extends Factory
{
    public function definition(): array
    {
        return [
            'area_id' => Area::factory(),
            'master_mould_id' => MasterMould::factory(),
            'model_size_id' => ModelSize::factory(),
            'customer_id' => Customer::factory(),
            'hari_ke' => (string) fake()->numberBetween(1, 30),
            'mesin' => fake()->bothify('MC-##'),
            'tanggal_output_sample' => fake()->date(),
            'tanggal_pengukuran' => fake()->date(),
            'tanggal_naik_mould' => fake()->date(),
            'dibuat_oleh' => fake()->name(),
            'diperiksa_oleh' => fake()->name(),
            'status' => 'draft',
        ];
    }
}
