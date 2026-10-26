<?php

namespace Database\Seeders;

use App\Models\MeasurementCondition;
use Illuminate\Database\Seeder;

class MeasurementConditionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'sebelum_cuci', 'label' => 'Sebelum Cuci', 'sort_order' => 1],
            ['code' => 'sesudah_cuci', 'label' => 'Sesudah Cuci', 'sort_order' => 2],
            ['code' => 'setelah_bakar', 'label' => 'Setelah Bakar', 'sort_order' => 3],
        ] as $condition) {
            MeasurementCondition::query()->updateOrCreate(
                ['code' => $condition['code']],
                [...$condition, 'is_active' => true],
            );
        }
    }
}
