<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Customer;
use App\Models\FormPengukuran;
use App\Models\FormPengukuranItem;
use App\Models\MasterMould;
use App\Models\MeasurementActual;
use App\Models\MeasurementCondition;
use App\Models\MeasurementConfiguration;
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

    public function test_configuration_can_be_grouped_by_an_optional_area(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::query()->where('nik', 'D260065')->firstOrFail();
        $area = Area::factory()->create();
        $customer = Customer::factory()->create();
        $modelSize = ModelSize::factory()->create();
        $condition = MeasurementCondition::factory()->create([
            'code' => 'sebelum_cuci',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('measurement-configurations.store'), [
            'areaId' => $area->id,
            'customerId' => $customer->id,
            'modelSizeId' => $modelSize->id,
            'isActive' => true,
            'fields' => [[
                'parameterName' => 'L',
                'pdfParameterName' => null,
                'condition' => $condition->code,
                'expectedValue' => '24',
                'description' => null,
                'sortOrder' => 1,
                'isActive' => true,
            ]],
        ]);

        $response->assertRedirect(route('measurement-configurations.index'));
        $this->assertDatabaseHas('measurement_configurations', [
            'area_id' => $area->id,
            'customer_id' => $customer->id,
            'model_size_id' => $modelSize->id,
        ]);
    }

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

    public function test_removing_a_used_field_deletes_it_and_preserves_the_actual_snapshot(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::query()->where('nik', 'D260065')->firstOrFail();
        $customer = Customer::factory()->create();
        $modelSize = ModelSize::factory()->create();
        $condition = MeasurementCondition::factory()->create(['code' => 'sebelum_cuci']);
        $configuration = MeasurementConfiguration::factory()->create([
            'customer_id' => $customer->id,
            'model_size_id' => $modelSize->id,
        ]);
        $retainedField = MeasurementConfigurationField::factory()->create([
            'measurement_configuration_id' => $configuration->id,
            'parameter_name' => 'L',
            'condition' => $condition->code,
            'sort_order' => 1,
        ]);
        $removedField = MeasurementConfigurationField::factory()->create([
            'measurement_configuration_id' => $configuration->id,
            'parameter_name' => 'W',
            'condition' => $condition->code,
            'sort_order' => 2,
        ]);
        $formPengukuran = FormPengukuran::factory()->create([
            'master_mould_id' => MasterMould::factory(),
            'customer_id' => $customer,
            'model_size_id' => $modelSize,
            'status' => 'disetujui',
        ]);
        $item = FormPengukuranItem::factory()->create([
            'form_pengukuran_id' => $formPengukuran,
            'model_size_id' => $modelSize,
            'measurement_configuration_id' => $configuration,
        ]);
        $actual = MeasurementActual::factory()->create([
            'form_pengukuran_item_id' => $item,
            'measurement_configuration_field_id' => $removedField,
            'condition' => $condition->code,
            'specification' => '20',
            'actual' => '21',
            'parameter_label' => 'W',
            'parameter_description' => 'Snapshot lama',
            'parameter_sort_order' => 2,
        ]);

        $response = $this->actingAs($user)->put(route('measurement-configurations.update', $configuration), [
            'customerId' => $customer->id,
            'modelSizeId' => $modelSize->id,
            'fields' => [[
                'parameterName' => $retainedField->parameter_name,
                'condition' => $retainedField->condition,
                'expectedValue' => (string) $retainedField->expected_value,
                'description' => $retainedField->description,
                'sortOrder' => 1,
            ]],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('measurement-configurations.index'));
        $this->assertDatabaseMissing('measurement_configuration_fields', ['id' => $removedField->id]);
        $this->assertDatabaseHas('measurement_actuals', [
            'id' => $actual->id,
            'measurement_configuration_field_id' => null,
            'actual' => 21,
            'parameter_label' => 'W',
            'parameter_description' => 'Snapshot lama',
        ]);
    }
}
