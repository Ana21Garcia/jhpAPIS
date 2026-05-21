<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('mantenimiento_productos')) {
            Schema::create('mantenimiento_productos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_mantenimiento');
                $table->unsignedBigInteger('id_producto');
                $table->integer('cantidad');
                $table->decimal('precio_venta', 10, 2);
                $table->timestamps();
                $table->index('id_mantenimiento');
                $table->index('id_producto');
            });
        }

        if (Schema::hasTable('mantenimiento_productos') && Schema::hasTable('mantenimiento')) {
            try {
                DB::statement('ALTER TABLE mantenimiento_productos ADD CONSTRAINT mantenimiento_productos_mantenimiento_fk FOREIGN KEY (id_mantenimiento) REFERENCES mantenimiento(id_mantenimiento)');
            } catch (\Throwable $e) {
                // La base local puede venir importada con tipos distintos; dejamos la tabla usable.
            }
        }

        if (Schema::hasTable('mantenimiento_productos') && Schema::hasTable('productos')) {
            try {
                DB::statement('ALTER TABLE mantenimiento_productos ADD CONSTRAINT mantenimiento_productos_productos_fk FOREIGN KEY (id_producto) REFERENCES productos(id_producto)');
            } catch (\Throwable $e) {
                // Compatibilidad con dumps que usan la tabla producto singular.
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_productos');
    }
};
