<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clientes')) {
            Schema::table('clientes', function (Blueprint $table) {
                if (!Schema::hasColumn('clientes', 'cli_direccion')) {
                    $table->text('cli_direccion')->nullable()->after('cli_correo');
                }
                if (!Schema::hasColumn('clientes', 'cli_password')) {
                    $table->string('cli_password', 255)->nullable()->after('cli_direccion');
                }
                if (!Schema::hasColumn('clientes', 'cli_estado')) {
                    $table->enum('cli_estado', ['Activo', 'Inactivo'])->default('Activo')->after('cli_password');
                }
            });
        }

        if (Schema::hasTable('empleados')) {
            Schema::table('empleados', function (Blueprint $table) {
                if (!Schema::hasColumn('empleados', 'emp_correo')) {
                    $table->string('emp_correo', 100)->nullable()->after('emp_telefono');
                }
                if (!Schema::hasColumn('empleados', 'emp_usuario')) {
                    $table->string('emp_usuario', 100)->nullable()->after('es_mecanico');
                }
            });

            if (Schema::hasColumn('empleados', 'emp_usuario') && Schema::hasColumn('empleados', 'emp_correo')) {
                DB::table('empleados')
                    ->whereNull('emp_usuario')
                    ->orWhere('emp_usuario', '')
                    ->orderBy('id_empleados')
                    ->get()
                    ->each(function ($empleado) {
                        DB::table('empleados')
                            ->where('id_empleados', $empleado->id_empleados)
                            ->update([
                                'emp_usuario' => $empleado->emp_correo ?: ('empleado' . $empleado->id_empleados),
                            ]);
                    });
            }
        }

        if (Schema::hasTable('ventas')) {
            Schema::table('ventas', function (Blueprint $table) {
                if (!Schema::hasColumn('ventas', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('ventas', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('detalle_ventas')) {
            Schema::table('detalle_ventas', function (Blueprint $table) {
                if (!Schema::hasColumn('detalle_ventas', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('detalle_ventas', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('producto') && Schema::hasTable('productos')) {
            DB::table('producto')->orderBy('id_producto')->get()->each(function ($producto) {
                DB::table('productos')->updateOrInsert(
                    ['pro_codigo' => $producto->pro_codigo],
                    [
                        'pro_nombre' => $producto->pro_nombre,
                        'pro_tipo' => $producto->pro_tipo,
                        'pro_marca' => $producto->pro_marca,
                        'pro_descripcion' => $producto->pro_descripcion,
                        'pro_precio_venta' => $producto->pro_precio_venta,
                        'pro_stock' => $producto->pro_stock,
                        'id_categoria' => $producto->id_categoria,
                        'id_proveedor' => $producto->id_proveedor,
                    ],
                );
            });
        }

        if (Schema::hasTable('control_caja') && Schema::hasTable('control_cajas')) {
            DB::table('control_caja')->orderBy('id_caja')->get()->each(function ($caja) {
                DB::table('control_cajas')->updateOrInsert(
                    ['id_caja' => $caja->id_caja],
                    [
                        'id_empleado' => $caja->id_empleado,
                        'fecha_apertura' => $caja->fecha_apertura,
                        'monto_inicial' => $caja->monto_inicial,
                        'fecha_cierre' => $caja->fecha_cierre,
                        'monto_final_esperado' => $caja->monto_final_esperado,
                        'monto_real_cierre' => $caja->monto_real_cierre,
                        'estado' => $caja->estado,
                    ],
                );
            });
        }
    }

    public function down(): void
    {
        //
    }
};
