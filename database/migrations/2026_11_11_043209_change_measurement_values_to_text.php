<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('measurement_configuration_fields', function (Blueprint $table): void {
            $table->text('expected_value')->nullable()->change();
        });

        Schema::table('measurement_actuals', function (Blueprint $table): void {
            $table->text('specification')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('measurement_configuration_fields', function (Blueprint $table): void {
            $table->decimal('expected_value', 12, 3)->nullable()->change();
        });

        Schema::table('measurement_actuals', function (Blueprint $table): void {
            $table->decimal('specification', 12, 3)->nullable()->change();
        });
    }
};
