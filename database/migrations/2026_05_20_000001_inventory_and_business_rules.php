<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('productos')) {
            Schema::table('productos', function (Blueprint $table) {
                if (!Schema::hasColumn('productos', 'pro_iva')) {
                    $table->decimal('pro_iva', 10, 2)->default(0)->after('pro_precio_venta');
                }
                if (!Schema::hasColumn('productos', 'pro_categoria')) {
                    $table->string('pro_categoria', 80)->nullable()->after('pro_stock');
                }
                if (!Schema::hasColumn('productos', 'pro_proveedor')) {
                    $table->string('pro_proveedor', 120)->nullable()->after('pro_categoria');
                }
            });
        }

        if (Schema::hasTable('clientes')) {
            Schema::table('clientes', function (Blueprint $table) {
                if (!Schema::hasColumn('clientes', 'cli_telefonos_extra')) {
                    $table->json('cli_telefonos_extra')->nullable()->after('cli_telefono');
                }
                if (!Schema::hasColumn('clientes', 'tipo_usuario')) {
                    $table->unsignedTinyInteger('tipo_usuario')->default(3)->after('cli_correo');
                }
            });
        }

        if (Schema::hasTable('empleados')) {
            Schema::table('empleados', function (Blueprint $table) {
                if (!Schema::hasColumn('empleados', 'tipo_usuario')) {
                    $table->unsignedTinyInteger('tipo_usuario')->default(2)->after('emp_rol');
                }
                if (!Schema::hasColumn('empleados', 'es_mecanico')) {
                    $table->boolean('es_mecanico')->default(false)->after('tipo_usuario');
                }
            });
        }

        if (Schema::hasTable('proveedores')) {
            Schema::table('proveedores', function (Blueprint $table) {
                if (!Schema::hasColumn('proveedores', 'productos_sucursal')) {
                    $table->json('productos_sucursal')->nullable()->after('prov_direccion');
                }
            });
        }

        if (Schema::hasTable('cotizaciones')) {
            Schema::table('cotizaciones', function (Blueprint $table) {
                if (!Schema::hasColumn('cotizaciones', 'cot_estado')) {
                    $table->string('cot_estado', 20)->default('Vigente')->after('cot_vigencia_dias');
                }
            });
        }

        if (!Schema::hasTable('inventarios')) {
            Schema::create('inventarios', function (Blueprint $table) {
                $table->increments('id_inventario');
                $table->unsignedInteger('id_producto')->nullable();
                $table->string('codigo_producto', 50);
                $table->string('nombre_producto', 120);
                $table->string('marca', 80)->nullable();
                $table->string('categoria', 80)->nullable();
                $table->integer('stock')->default(0);
                $table->decimal('precio_unitario', 10, 2)->default(0);
                $table->decimal('iva', 10, 2)->default(0);
                $table->decimal('precio_total', 10, 2)->default(0);
                $table->unsignedInteger('id_proveedor')->nullable();
                $table->string('proveedor', 120)->nullable();
                $table->timestamps();
                $table->unique(['codigo_producto', 'marca', 'categoria'], 'inventarios_producto_unico');
                $table->index('stock');
            });
        }

        if (Schema::hasTable('productos') && Schema::hasTable('inventarios')) {
            DB::table('productos')->orderBy('id_producto')->get()->each(function ($producto) {
                $precio = (float) ($producto->pro_precio_venta ?? 0);
                $iva = (float) ($producto->pro_iva ?? 0);
                DB::table('inventarios')->updateOrInsert(
                    [
                        'codigo_producto' => $producto->pro_codigo ?? ('PROD-' . $producto->id_producto),
                        'marca' => $producto->pro_marca,
                        'categoria' => $producto->pro_categoria,
                    ],
                    [
                        'id_producto' => $producto->id_producto,
                        'nombre_producto' => $producto->pro_nombre ?? 'Producto',
                        'stock' => (int) ($producto->pro_stock ?? 0),
                        'precio_unitario' => $precio,
                        'iva' => $iva,
                        'precio_total' => $precio + $iva,
                        'id_proveedor' => $producto->id_proveedor ?? null,
                        'proveedor' => $producto->pro_proveedor ?? null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
