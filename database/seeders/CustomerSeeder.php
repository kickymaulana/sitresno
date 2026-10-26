<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customerModelSizes = $this->customerModelSizes();

        DB::transaction(function () use ($customerModelSizes): void {
            foreach (array_keys($customerModelSizes) as $customerName) {
                Customer::query()->updateOrCreate(
                    ['name' => $customerName],
                    ['description' => null],
                );
            }
        });
    }

    /** @return array<string, list<string>> */
    private function customerModelSizes(): array
    {
        $source = json_decode(
            (string) file_get_contents(database_path('data/customer-model-sizes.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        if (! is_array($source)) {
            throw new RuntimeException('The bundled customer model/size source must contain an object.');
        }

        return $source;
    }
}
