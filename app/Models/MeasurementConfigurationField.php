<?php

namespace App\Models;

use Database\Factories\MeasurementConfigurationFieldFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['measurement_configuration_id', 'parameter_name', 'pdf_parameter_name', 'condition', 'expected_value', 'description', 'sort_order', 'is_active'])]
class MeasurementConfigurationField extends Model
{
    /** @use HasFactory<MeasurementConfigurationFieldFactory> */
    use HasFactory;

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(MeasurementConfiguration::class, 'measurement_configuration_id');
    }

    public function actuals(): HasMany
    {
        return $this->hasMany(MeasurementActual::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
