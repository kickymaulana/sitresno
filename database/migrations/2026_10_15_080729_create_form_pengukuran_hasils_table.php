<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_pengukuran_hasils', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('form_pengukuran_id')->constrained()->cascadeOnDelete();
            $table->string('kondisi');
            $table->text('remarks')->nullable();

            foreach (['h', 'l', 'pc_10mm', 'pc_middle', 'upper_palm_width', 'lower_palm_width', 'wc', 'sl_titik', 'fl_1', 'fl_2', 'fl_3', 'fl_4', 'fl_5', 'fc_1', 'fc_2', 'fc_3', 'fc_4', 'fc_5', 'fc_internal_1', 'fc_internal_2', 'fc_internal_3', 'fc_internal_4', 'fc_internal_5', 'lt', 'd1', 'd2', 'pt', 'span', 'kl', 'tt', 'prbt_t', 'm', 'beading_267', 'beading_222', 'tb', 'w'] as $measurement) {
                $table->decimal($measurement, 10, 3)->nullable();
            }

            $table->unique(['form_pengukuran_id', 'kondisi']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_pengukuran_hasils');
    }
};
