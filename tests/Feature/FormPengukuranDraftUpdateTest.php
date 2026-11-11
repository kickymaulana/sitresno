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
use Tests\TestCase;

class FormPengukuranDraftUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_update_uses_latest_active_configuration_fields(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::query()->where('nik', 'D260065')->firstOrFail();
        $condition = MeasurementCondition::factory()->create([
            'code' => 'sebelum_cuci',
            'is_active' => true,
        ]);
        $area = Area::factory()->create();
        $masterMould = MasterMould::factory()->create();
        $customer = Customer::factory()->create();
        $modelSize = ModelSize::factory()->create();
        $configuration = MeasurementConfiguration::factory()->create([
            'customer_id' => $customer,
            'model_size_id' => $modelSize,
            'is_active' => true,
        ]);
        $retainedField = MeasurementConfigurationField::factory()->create([
            'measurement_configuration_id' => $configuration,
            'parameter_name' => 'L',
            'condition' => $condition->code,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $removedField = MeasurementConfigurationField::factory()->create([
            'measurement_configuration_id' => $configuration,
            'parameter_name' => 'Field Lama',
            'condition' => $condition->code,
            'sort_order' => 2,
            'is_active' => false,
        ]);
        $newField = MeasurementConfigurationField::factory()->create([
            'measurement_configuration_id' => $configuration,
            'parameter_name' => 'Field Baru',
            'condition' => $condition->code,
            'sort_order' => 3,
            'is_active' => true,
        ]);
        $formPengukuran = FormPengukuran::factory()->create([
            'area_id' => $area,
            'master_mould_id' => $masterMould,
            'model_size_id' => $modelSize,
            'customer_id' => $customer,
            'tanggal_pengukuran' => '2026-11-11',
            'status' => 'draft',
        ]);
        $item = FormPengukuranItem::factory()->create([
            'form_pengukuran_id' => $formPengukuran,
            'model_size_id' => $modelSize,
            'measurement_configuration_id' => $configuration,
            'model_size_name' => $modelSize->displayName(),
            'sort_order' => 1,
        ]);
        MeasurementActual::factory()->create([
            'form_pengukuran_item_id' => $item,
            'measurement_configuration_field_id' => $retainedField,
            'condition' => $condition->code,
            'parameter_label' => $retainedField->parameter_name,
            'parameter_sort_order' => 1,
            'actual' => null,
        ]);
        $removedActual = MeasurementActual::factory()->create([
            'form_pengukuran_item_id' => $item,
            'measurement_configuration_field_id' => $removedField,
            'condition' => $condition->code,
            'parameter_label' => $removedField->parameter_name,
            'parameter_sort_order' => 2,
        ]);

        $response = $this->actingAs($user)->put(route('form-pengukurans.update', $formPengukuran), [
            'areaId' => $area->id,
            'masterMouldId' => $masterMould->id,
            'customerId' => $customer->id,
            'hariKe' => '1',
            'mesin' => 'MC-01',
            'tanggalOutputSample' => null,
            'tanggalPengukuran' => '2026-11-11',
            'tanggalNaikMould' => null,
            'dibuatOleh' => 'Inspector',
            'diperiksaOleh' => null,
            'status' => 'draft',
            'items' => [[
                'modelSizeId' => $modelSize->id,
                'remarks' => null,
                'actuals' => [
                    ['configurationFieldId' => $retainedField->id, 'actual' => 24],
                    ['configurationFieldId' => $newField->id, 'actual' => null],
                ],
            ]],
        ]);

        $response->assertRedirect(route('form-pengukurans.show', $formPengukuran));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('measurement_actuals', [
            'form_pengukuran_item_id' => $item->id,
            'measurement_configuration_field_id' => $retainedField->id,
            'actual' => 24,
        ]);
        $this->assertDatabaseHas('measurement_actuals', [
            'form_pengukuran_item_id' => $item->id,
            'measurement_configuration_field_id' => $newField->id,
        ]);
        $this->assertDatabaseMissing('measurement_actuals', ['id' => $removedActual->id]);
    }
}
