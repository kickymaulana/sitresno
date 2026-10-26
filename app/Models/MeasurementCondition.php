<?php

namespace App\Models;

use Database\Factories\MeasurementConditionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'label', 'sort_order', 'is_active'])]
class MeasurementCondition extends Model
{
    /** @use HasFactory<MeasurementConditionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
