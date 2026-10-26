<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('model_sizes', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('id');
            $table->string('model')->nullable()->change();
            $table->string('size')->nullable()->change();
            $table->string('full_name')->nullable()->change();
        });

        Schema::table('form_pengukurans', function (Blueprint $table): void {
            $table->dropForeign(['model_size_id']);
            $table->unsignedBigInteger('model_size_id')->nullable()->change();
            $table->index(['customer_id', 'tanggal_pengukuran'], 'form_pengukurans_customer_date_index');
        });

        Schema::create('form_pengukuran_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('form_pengukuran_id')->constrained()->cascadeOnDelete();
            $table->foreignId('model_size_id')->constrained()->restrictOnDelete();
            $table->string('model_size_name');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('sort_order');
            $table->timestamps();
            $table->unique(['form_pengukuran_id', 'model_size_id']);
        });

        Schema::create('measurement_actuals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('form_pengukuran_item_id')->constrained()->cascadeOnDelete();
            $table->string('condition');
            $table->decimal('specification', 12, 3)->nullable();
            $table->decimal('actual', 12, 3)->nullable();
            $table->text('parameter_label');
            $table->text('parameter_description')->nullable();
            $table->unsignedInteger('parameter_sort_order');
            $table->boolean('parameter_is_starred')->default(false);
            $table->timestamps();
            $table->index(['form_pengukuran_item_id', 'condition', 'parameter_sort_order'], 'measurement_actuals_order_index');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('measurement_actuals');
        Schema::dropIfExists('form_pengukuran_items');

        Schema::table('form_pengukurans', function (Blueprint $table): void {
            $table->dropIndex('form_pengukurans_customer_date_index');
            $table->unsignedBigInteger('model_size_id')->nullable(false)->change();
            $table->foreign('model_size_id')->references('id')->on('model_sizes')->restrictOnDelete();
        });

        Schema::table('model_sizes', function (Blueprint $table): void {
            $table->dropColumn('name');
            $table->string('model')->nullable(false)->change();
            $table->string('size')->nullable(false)->change();
            $table->string('full_name')->nullable(false)->change();
        });

    }
};
