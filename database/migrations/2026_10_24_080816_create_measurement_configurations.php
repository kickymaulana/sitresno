<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurement_configurations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('model_size_id')->constrained()->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['customer_id', 'model_size_id'], 'measurement_configurations_customer_model_unique');
        });

        Schema::create('measurement_configuration_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('measurement_configuration_id')
                ->constrained(indexName: 'mcf_config_fk')
                ->cascadeOnDelete();
            $table->string('parameter_name');
            $table->string('condition');
            $table->decimal('expected_value', 12, 3)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(
                ['measurement_configuration_id', 'parameter_name', 'condition'],
                'measurement_configuration_fields_unique',
            );
            $table->index(
                ['measurement_configuration_id', 'is_active', 'sort_order'],
                'measurement_configuration_fields_order_index',
            );
        });

        Schema::table('form_pengukuran_items', function (Blueprint $table): void {
            $table->foreignId('measurement_configuration_id')
                ->nullable()
                ->after('model_size_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('measurement_actuals', function (Blueprint $table): void {
            $table->foreignId('measurement_configuration_field_id')
                ->nullable()
                ->after('form_pengukuran_item_id')
                ->constrained()
                ->nullOnDelete();
            $table->unique(['form_pengukuran_item_id', 'measurement_configuration_field_id'], 'measurement_actuals_configuration_field_unique');
        });
    }

    public function down(): void
    {
        Schema::table('measurement_actuals', function (Blueprint $table): void {
            $table->dropUnique('measurement_actuals_configuration_field_unique');
            $table->dropConstrainedForeignId('measurement_configuration_field_id');
        });

        Schema::table('form_pengukuran_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('measurement_configuration_id');
        });

        Schema::dropIfExists('measurement_configuration_fields');
        Schema::dropIfExists('measurement_configurations');
    }
};
