<?php

namespace App\Models;

use Database\Factories\MeasurementActualFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['form_pengukuran_item_id', 'measurement_configuration_field_id', 'condition', 'specification', 'actual', 'parameter_label', 'parameter_description', 'parameter_sort_order', 'parameter_is_starred'])]
class MeasurementActual extends Model
{
    /** @use HasFactory<MeasurementActualFactory> */
    use HasFactory;

    public function item(): BelongsTo
    {
        return $this->belongsTo(FormPengukuranItem::class, 'form_pengukuran_item_id');
    }

    public function configurationField(): BelongsTo
    {
        return $this->belongsTo(MeasurementConfigurationField::class, 'measurement_configuration_field_id');
    }

    protected function casts(): array
    {
        return [
            'actual' => 'decimal:3',
            'parameter_is_starred' => 'boolean',
        ];
    }
}
