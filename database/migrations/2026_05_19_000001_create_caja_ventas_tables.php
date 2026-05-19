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
        if (!Schema::hasTable('control_cajas')) {
            Schema::create('control_cajas', function (Blueprint $table) {
                $table->increments('id_caja');
                $table->unsignedInteger('id_empleado')->nullable();
                $table->dateTime('fecha_apertura')->nullable();
                $table->decimal('monto_inicial', 10, 2);
                $table->dateTime('fecha_cierre')->nullable();
                $table->decimal('monto_final_esperado', 10, 2)->nullable();
                $table->decimal('monto_real_cierre', 10, 2)->nullable();
                $table->enum('estado', ['Abierta', 'Cerrada'])->default('Abierta');
                $table->timestamps();
                $table->index('estado');
            });
        }

        if (!Schema::hasTable('ventas')) {
            Schema::create('ventas', function (Blueprint $table) {
                $table->increments('id_venta');
                $table->unsignedInteger('id_cliente')->nullable();
                $table->unsignedInteger('id_empleado')->nullable();
                $table->unsignedInteger('id_caja')->nullable();
                $table->timestamp('ven_fecha')->nullable();
                $table->decimal('ven_total', 10, 2)->default(0);
                $table->string('tipo_pago', 30)->nullable();
                $table->timestamps();
                $table->index('ven_fecha');
                $table->index('id_caja');
            });
        }

        if (!Schema::hasTable('detalle_ventas')) {
            Schema::create('detalle_ventas', function (Blueprint $table) {
                $table->increments('id_detalle');
                $table->unsignedInteger('id_venta')->nullable();
                $table->unsignedInteger('id_producto')->nullable();
                $table->integer('det_cantidad')->default(1);
                $table->decimal('det_precio_unitario', 10, 2)->default(0);
                $table->timestamps();
                $table->index('id_venta');
                $table->index('id_producto');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('control_cajas');
    }
};
