<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik')->nullable()->unique()->after('id');
            $table->boolean('is_approved')->default(false)->after('password');
            $table->timestamp('approved_at')->nullable()->after('is_approved');
            $table->timestamp('last_login_at')->nullable()->after('approved_at');
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nik']);
            $table->dropColumn(['nik', 'is_approved', 'approved_at', 'last_login_at']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
