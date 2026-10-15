<?php

namespace App\Models;

use Database\Factories\FormPengukuranFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['area_id', 'master_mould_id', 'model_size_id', 'customer_id', 'hari_ke', 'mesin', 'tanggal_output_sample', 'tanggal_pengukuran', 'tanggal_naik_mould', 'dibuat_oleh', 'diperiksa_oleh', 'status'])]
class FormPengukuran extends Model
{
    /** @use HasFactory<FormPengukuranFactory> */
    use HasFactory;

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function masterMould(): BelongsTo
    {
        return $this->belongsTo(MasterMould::class);
    }

    public function modelSize(): BelongsTo
    {
        return $this->belongsTo(ModelSize::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function hasils(): HasMany
    {
        return $this->hasMany(FormPengukuranHasil::class);
    }

    protected function casts(): array
    {
        return [
            'tanggal_output_sample' => 'date',
            'tanggal_pengukuran' => 'date',
            'tanggal_naik_mould' => 'date',
        ];
    }
}
