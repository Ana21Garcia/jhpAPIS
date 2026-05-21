<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnsureCatalogTables
{
    public static function ensure(): void
    {
        if (!Schema::hasTable('categorias')) {
            Schema::create('categorias', function (Blueprint $table) {
                $table->increments('id_categoria');
                $table->string('cat_nombre', 50);
                $table->text('cat_descripcion')->nullable();
                $table->timestamps();
                $table->index('cat_nombre');
            });
        }

        if (!Schema::hasTable('clientes')) {
            Schema::create('clientes', function (Blueprint $table) {
                $table->increments('id_cliente');
                $table->string('cli_nombre', 100);
                $table->string('cli_apaterno', 50)->nullable();
                $table->string('cli_amaterno', 50)->nullable();
                $table->string('cli_telefono', 15)->nullable();
                $table->string('cli_correo', 100)->nullable();
                $table->text('cli_direccion')->nullable();
                $table->string('cli_password', 255)->nullable();
                $table->json('cli_telefonos_extra')->nullable();
                $table->unsignedTinyInteger('tipo_usuario')->default(3);
                $table->enum('cli_estado', ['Activo', 'Inactivo'])->default('Activo');
                $table->timestamp('cli_fecha_registro')->nullable();
                $table->timestamps();
                $table->index('cli_correo');
                $table->index('cli_telefono');
            });
        }

        if (!Schema::hasTable('proveedores')) {
            Schema::create('proveedores', function (Blueprint $table) {
                $table->increments('id_proveedor');
                $table->string('prov_nombre', 100);
                $table->string('prov_contacto', 100)->nullable();
                $table->string('prov_telefono', 15)->nullable();
                $table->string('prov_email', 100)->nullable();
                $table->text('prov_direccion')->nullable();
                $table->timestamps();
                $table->index('prov_email');
            });
        }

        if (!Schema::hasTable('empleados')) {
            Schema::create('empleados', function (Blueprint $table) {
                $table->increments('id_empleados');
                $table->string('emp_nombre', 50);
                $table->string('emp_apaterno', 50);
                $table->string('emp_amaterno', 50)->nullable();
                $table->string('emp_telefono', 15)->nullable();
                $table->string('emp_correo', 100)->nullable();
                $table->text('emp_direccion')->nullable();
                $table->string('emp_rol', 30)->default('Empleado');
                $table->unsignedTinyInteger('tipo_usuario')->default(2);
                $table->boolean('es_mecanico')->default(false);
                $table->string('emp_usuario', 50)->unique();
                $table->string('emp_password', 255);
                $table->string('emp_estado', 20)->default('Activo');
                $table->timestamps();
                $table->index('emp_estado');
            });
        }

        if (!Schema::hasTable('productos')) {
            Schema::create('productos', function (Blueprint $table) {
                $table->increments('id_producto');
                $table->string('pro_codigo', 50)->unique();
                $table->string('pro_nombre', 100);
                $table->string('pro_tipo', 50)->nullable();
                $table->string('pro_marca', 50)->nullable();
                $table->text('pro_descripcion')->nullable();
                $table->decimal('pro_precio_venta', 10, 2)->default(0);
                $table->integer('pro_stock')->default(0);
                $table->unsignedInteger('id_categoria')->nullable();
                $table->unsignedInteger('id_proveedor')->nullable();
                $table->timestamps();
                $table->index('pro_codigo');
                $table->index('pro_stock');
            });
        }

        if (!Schema::hasTable('servicios')) {
            Schema::create('servicios', function (Blueprint $table) {
                $table->increments('id_servicio');
                $table->string('ser_nombre', 100);
                $table->text('ser_descripcion')->nullable();
                $table->decimal('ser_precio_mano_obra', 10, 2)->default(0);
                $table->unsignedInteger('id_categoria')->nullable();
                $table->timestamps();
                $table->index('ser_nombre');
            });
        }
    }
}
