<?php

namespace App\Models;

use Database\Factories\FormPengukuranItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['form_pengukuran_id', 'model_size_id', 'measurement_configuration_id', 'model_size_name', 'remarks', 'sort_order'])]
class FormPengukuranItem extends Model
{
    /** @use HasFactory<FormPengukuranItemFactory> */
    use HasFactory;

    public function formPengukuran(): BelongsTo
    {
        return $this->belongsTo(FormPengukuran::class);
    }

    public function modelSize(): BelongsTo
    {
        return $this->belongsTo(ModelSize::class);
    }

    public function measurementConfiguration(): BelongsTo
    {
        return $this->belongsTo(MeasurementConfiguration::class);
    }

    public function actuals(): HasMany
    {
        return $this->hasMany(MeasurementActual::class)->orderBy('condition')->orderBy('parameter_sort_order');
    }
}
