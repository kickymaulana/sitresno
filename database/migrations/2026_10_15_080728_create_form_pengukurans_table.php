<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_pengukurans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('area_id')->constrained()->restrictOnDelete();
            $table->foreignId('master_mould_id')->constrained()->restrictOnDelete();
            $table->foreignId('model_size_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('hari_ke')->nullable();
            $table->string('mesin')->nullable();
            $table->date('tanggal_output_sample')->nullable();
            $table->date('tanggal_pengukuran')->nullable();
            $table->date('tanggal_naik_mould')->nullable();
            $table->string('dibuat_oleh')->nullable();
            $table->string('diperiksa_oleh')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_pengukurans');
    }
};
