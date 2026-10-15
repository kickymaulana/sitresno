<?php

namespace App\Models;

use Database\Factories\AreaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Area extends Model
{
    /** @use HasFactory<AreaFactory> */
    use HasFactory;

    public function formPengukurans(): HasMany
    {
        return $this->hasMany(FormPengukuran::class);
    }
}
