<?php

namespace App\Models;

use Database\Factories\MasterMouldFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'description'])]
class MasterMould extends Model
{
    /** @use HasFactory<MasterMouldFactory> */
    use HasFactory;

    public function formPengukurans(): HasMany
    {
        return $this->hasMany(FormPengukuran::class);
    }
}
