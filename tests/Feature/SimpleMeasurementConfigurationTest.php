<?php

namespace Tests\Feature;

use App\Models\MeasurementConfigurationField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SimpleMeasurementConfigurationTest extends TestCase
{
    use RefreshDatabase;

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
