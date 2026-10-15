<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Pabrik Dalu 1', 'Pabrik Dalu 2'] as $name) {
            Area::query()->firstOrCreate(['name' => $name]);
        }
    }
}
