<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('citas')) {
            Schema::create('citas', function (Blueprint $table) {
                $table->increments('id_cita');
                $table->unsignedInteger('id_cliente')->nullable();
                $table->unsignedInteger('id_empleado')->nullable();
                $table->dateTime('cita_fecha_programada');
                $table->string('cita_motivo', 255)->nullable();
                $table->string('cita_estado', 30)->default('Pendiente');
                $table->text('cita_notas')->nullable();
                $table->timestamps();
                $table->index(['id_cliente', 'cita_estado'], 'citas_cliente_estado_index');
                $table->index('cita_fecha_programada', 'citas_fecha_index');
            });

            return;
        }

        Schema::table('citas', function (Blueprint $table) {
            if (!Schema::hasColumn('citas', 'id_cliente')) {
                $table->unsignedInteger('id_cliente')->nullable()->after('id_cita');
            }
            if (!Schema::hasColumn('citas', 'id_empleado')) {
                $table->unsignedInteger('id_empleado')->nullable()->after('id_cliente');
            }
            if (!Schema::hasColumn('citas', 'cita_fecha_programada')) {
                $table->dateTime('cita_fecha_programada')->nullable()->after('id_empleado');
            }
            if (!Schema::hasColumn('citas', 'cita_motivo')) {
                $table->string('cita_motivo', 255)->nullable()->after('cita_fecha_programada');
            }
            if (!Schema::hasColumn('citas', 'cita_estado')) {
                $table->string('cita_estado', 30)->default('Pendiente')->after('cita_motivo');
            }
            if (!Schema::hasColumn('citas', 'cita_notas')) {
                $table->text('cita_notas')->nullable()->after('cita_estado');
            }
            if (!Schema::hasColumn('citas', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('citas', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Compatibility migration: keep existing appointment data intact.
    }
};
