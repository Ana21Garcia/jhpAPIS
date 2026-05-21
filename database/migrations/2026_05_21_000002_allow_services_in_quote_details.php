<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('detalle_cotizaciones')) {
            return;
        }

        Schema::table('detalle_cotizaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('detalle_cotizaciones', 'id_servicio')) {
                $table->unsignedInteger('id_servicio')->nullable()->after('id_producto');
            }
        });

        try {
            DB::statement('ALTER TABLE detalle_cotizaciones MODIFY id_producto INT(11) NULL');
        } catch (\Throwable $e) {
            // Some local MySQL setups keep a compatible nullable definition already.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('detalle_cotizaciones')) {
            return;
        }

        Schema::table('detalle_cotizaciones', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_cotizaciones', 'id_servicio')) {
                $table->dropColumn('id_servicio');
            }
        });
    }
};
