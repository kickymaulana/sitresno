<?php

namespace App\Models;

use Database\Factories\FormPengukuranHasilFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['form_pengukuran_id', 'kondisi', 'remarks', 'h', 'l', 'pc_10mm', 'pc_middle', 'upper_palm_width', 'lower_palm_width', 'wc', 'sl_titik', 'fl_1', 'fl_2', 'fl_3', 'fl_4', 'fl_5', 'fc_1', 'fc_2', 'fc_3', 'fc_4', 'fc_5', 'fc_internal_1', 'fc_internal_2', 'fc_internal_3', 'fc_internal_4', 'fc_internal_5', 'lt', 'd1', 'd2', 'pt', 'span', 'kl', 'tt', 'prbt_t', 'm', 'beading_267', 'beading_222', 'tb', 'w'])]
class FormPengukuranHasil extends Model
{
    /** @use HasFactory<FormPengukuranHasilFactory> */
    use HasFactory;

    public const CONDITIONS = ['sebelum_cuci', 'sesudah_cuci', 'setelah_bakar'];

    public const MEASUREMENTS = ['h', 'l', 'pc_10mm', 'pc_middle', 'upper_palm_width', 'lower_palm_width', 'wc', 'sl_titik', 'fl_1', 'fl_2', 'fl_3', 'fl_4', 'fl_5', 'fc_1', 'fc_2', 'fc_3', 'fc_4', 'fc_5', 'fc_internal_1', 'fc_internal_2', 'fc_internal_3', 'fc_internal_4', 'fc_internal_5', 'lt', 'd1', 'd2', 'pt', 'span', 'kl', 'tt', 'prbt_t', 'm', 'beading_267', 'beading_222', 'tb', 'w'];

    public function formPengukuran(): BelongsTo
    {
        return $this->belongsTo(FormPengukuran::class);
    }

    protected function casts(): array
    {
        return array_fill_keys(self::MEASUREMENTS, 'decimal:3');
    }
}
