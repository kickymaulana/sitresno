<?php

namespace App\Models;

use Database\Factories\ModelSizeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'model', 'size', 'full_name', 'description'])]
class ModelSize extends Model
{
    /** @use HasFactory<ModelSizeFactory> */
    use HasFactory;

    public function measurementConfigurations(): HasMany
    {
        return $this->hasMany(MeasurementConfiguration::class);
    }

    public function formPengukurans(): HasMany
    {
        return $this->hasMany(FormPengukuran::class);
    }

    public function formPengukuranItems(): HasMany
    {
        return $this->hasMany(FormPengukuranItem::class);
    }

    public function displayName(): string
    {
        return $this->name ?? $this->full_name ?? trim(implode(' ', array_filter([$this->model, $this->size])));
    }
}
