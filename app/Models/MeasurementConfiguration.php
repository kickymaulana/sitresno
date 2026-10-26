<?php

namespace App\Models;

use Database\Factories\MeasurementConfigurationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['customer_id', 'model_size_id', 'is_active'])]
class MeasurementConfiguration extends Model
{
    /** @use HasFactory<MeasurementConfigurationFactory> */
    use HasFactory;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function modelSize(): BelongsTo
    {
        return $this->belongsTo(ModelSize::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(MeasurementConfigurationField::class)->orderBy('sort_order')->orderBy('id');
    }

    public function formPengukuranItems(): HasMany
    {
        return $this->hasMany(FormPengukuranItem::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
