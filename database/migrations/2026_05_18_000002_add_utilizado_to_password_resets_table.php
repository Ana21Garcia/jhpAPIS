<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('password_resets')) {
            return;
        }

        Schema::table('password_resets', function (Blueprint $table) {
            if (! Schema::hasColumn('password_resets', 'utilizado')) {
                $table->boolean('utilizado')->default(false)->after('fecha_expiracion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('password_resets')) {
            return;
        }

        Schema::table('password_resets', function (Blueprint $table) {
            if (Schema::hasColumn('password_resets', 'utilizado')) {
                $table->dropColumn('utilizado');
            }
        });
    }
};
