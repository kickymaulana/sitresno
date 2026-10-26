<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\MeasurementConfiguration;
use App\Models\ModelSize;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ModelSizeSeeder extends Seeder
{
    public function run(): void
    {
        $customerModelSizes = $this->customerModelSizes();
        DB::transaction(function () use ($customerModelSizes): void {
            foreach ($customerModelSizes as $customerName => $modelSizeNames) {
                $customer = Customer::query()->where('name', $customerName)->firstOrFail();

                foreach ($modelSizeNames as $modelSizeName) {
                    $modelSize = ModelSize::query()->updateOrCreate(
                        ['name' => $modelSizeName],
                        [

                            'full_name' => $modelSizeName,
                            'description' => null,
                        ],
                    );

                    MeasurementConfiguration::query()->updateOrCreate(
                        [
                            'customer_id' => $customer->id,
                            'model_size_id' => $modelSize->id,
                        ],
                        ['is_active' => true],
                    );
                }
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
