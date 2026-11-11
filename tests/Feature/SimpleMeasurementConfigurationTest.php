<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MeasurementCondition;
use App\Models\MeasurementConfigurationField;
use App\Models\ModelSize;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SimpleMeasurementConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_parameter_and_condition_errors_identify_each_field(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::query()->where('nik', 'D260065')->firstOrFail();
        $customer = Customer::factory()->create();
        $modelSize = ModelSize::factory()->create();
        $condition = MeasurementCondition::factory()->create([
            'code' => 'sebelum_cuci',
            'label' => 'Sebelum Cuci',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->from(route('measurement-configurations.create'))
            ->post(route('measurement-configurations.store'), [
                'customerId' => $customer->id,
                'modelSizeId' => $modelSize->id,
                'isActive' => true,
                'fields' => [
                    [
                        'parameterName' => 'L',
                        'condition' => $condition->code,
                        'expectedValue' => '24',
                        'description' => null,
                        'sortOrder' => 1,
                        'isActive' => true,
                    ],
                    [
                        'parameterName' => ' l ',
                        'condition' => $condition->code,
                        'expectedValue' => '25',
                        'description' => null,
                        'sortOrder' => 2,
                        'isActive' => true,
                    ],
                ],
            ]);

        $response->assertRedirect(route('measurement-configurations.create'));
        $response->assertSessionHasErrors([
            'fields',
            'fields.0.parameterName' => 'Duplikat dengan Field 2: L pada tahap Sebelum Cuci.',
            'fields.0.condition' => 'Duplikat dengan Field 2: L pada tahap Sebelum Cuci.',
            'fields.1.parameterName' => 'Duplikat dengan Field 1: l pada tahap Sebelum Cuci.',
            'fields.1.condition' => 'Duplikat dengan Field 1: l pada tahap Sebelum Cuci.',
        ]);
        $this->assertDatabaseCount('measurement_configurations', 0);
    }

    public function test_measurement_field_stores_its_typed_parameter_name_directly(): void
    {
        $field = MeasurementConfigurationField::factory()->create([
            'parameter_name' => 'BEADING',
            'condition' => 'sebelum_cuci',
        ]);

        $this->assertDatabaseHas('measurement_configuration_fields', [
            'id' => $field->id,
            'parameter_name' => 'BEADING',
            'condition' => 'sebelum_cuci',
        ]);
        $this->assertFalse(
            Schema::hasColumn('measurement_configuration_fields', 'measurement_parameter_id'),
        );
    }
}
