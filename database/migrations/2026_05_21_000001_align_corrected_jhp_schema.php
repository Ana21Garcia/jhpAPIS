<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->alignTables();
        $this->seedCatalogData();
        $this->syncProductTables();
    }

    private function alignTables(): void
    {
        if (Schema::hasTable('empleados')) {
            Schema::table('empleados', function (Blueprint $table) {
                if (!Schema::hasColumn('empleados', 'tipo_usuario')) {
                    $table->unsignedTinyInteger('tipo_usuario')->default(2)->after('emp_rol');
                }
                if (!Schema::hasColumn('empleados', 'es_mecanico')) {
                    $table->boolean('es_mecanico')->default(false)->after('tipo_usuario');
                }
                if (!Schema::hasColumn('empleados', 'emp_usuario')) {
                    $table->string('emp_usuario', 100)->nullable()->after('es_mecanico');
                }
            });
        }

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

        if (Schema::hasTable('cotizaciones')) {
            Schema::table('cotizaciones', function (Blueprint $table) {
                if (!Schema::hasColumn('cotizaciones', 'cot_estado')) {
                    $table->string('cot_estado', 20)->default('Vigente')->after('cot_vigencia_dias');
                }
                if (!Schema::hasColumn('cotizaciones', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('cotizaciones', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        if (!Schema::hasTable('detalle_cotizaciones')) {
            Schema::create('detalle_cotizaciones', function (Blueprint $table) {
                $table->increments('id_detalle_cotizacion');
                $table->unsignedInteger('id_cotizacion');
                $table->unsignedInteger('id_producto');
                $table->integer('det_cantidad');
                $table->decimal('det_precio_unitario', 10, 2);
                $table->index('id_cotizacion');
                $table->index('id_producto');
            });
        } else {
            Schema::table('detalle_cotizaciones', function (Blueprint $table) {
                if (!Schema::hasColumn('detalle_cotizaciones', 'id_detalle_cotizacion')) {
                    $table->increments('id_detalle_cotizacion')->first();
                }
                if (!Schema::hasColumn('detalle_cotizaciones', 'id_cotizacion')) {
                    $table->unsignedInteger('id_cotizacion')->nullable();
                }
                if (!Schema::hasColumn('detalle_cotizaciones', 'id_producto')) {
                    $table->unsignedInteger('id_producto')->nullable();
                }
                if (!Schema::hasColumn('detalle_cotizaciones', 'det_cantidad')) {
                    $table->integer('det_cantidad')->default(1);
                }
                if (!Schema::hasColumn('detalle_cotizaciones', 'det_precio_unitario')) {
                    $table->decimal('det_precio_unitario', 10, 2)->default(0);
                }
            });
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
    }

    private function seedCatalogData(): void
    {
        foreach ([
            [1, 'Motor', 'Pistones, anillos, biela, empaques y componentes internos'],
            [2, 'Frenos', 'Balatas, discos, mangueras y liquido de frenos'],
            [3, 'Electrico', 'Baterias, bujias, estatores y cableado'],
            [4, 'Accesorios', 'Cascos, guantes, espejos y elementos esteticos'],
            [5, 'Mantenimiento General', 'Servicios preventivos y correctivos programados'],
        ] as [$id, $nombre, $descripcion]) {
            DB::table('categorias')->updateOrInsert(
                ['id_categoria' => $id],
                ['cat_nombre' => $nombre, 'cat_descripcion' => $descripcion],
            );
        }

        foreach ([
            [1, 'Refaccionaria MotoExpress', 'Ing. Javier Arce', '5551112222', 'ventas@motoexpress.com', 'Av. Central #45, Col. Centro'],
            [2, 'Accesorios BikerZone', 'Lic. Elena Ramos', '5553334444', 'contacto@bikerzone.com', 'Calle Victoria #102, Industrial'],
            [3, 'Distribuidora Italika Oficial', 'Soporte Comercial', '5555556666', 'mayoreo@italika.mx', 'Parque Industrial Norte, Bodega 4'],
        ] as [$id, $nombre, $contacto, $telefono, $email, $direccion]) {
            DB::table('proveedores')->updateOrInsert(
                ['id_proveedor' => $id],
                [
                    'prov_nombre' => $nombre,
                    'prov_contacto' => $contacto,
                    'prov_telefono' => $telefono,
                    'prov_email' => $email,
                    'prov_direccion' => $direccion,
                ],
            );
        }

        foreach ([
            [1, 'Afinacion Completa', 'Lavado de carburador/inyector, cambio de bujia, cambio de aceite y filtro', 350.00, 5],
            [2, 'Cambio de Balatas Delanteras', 'Desmontaje, limpieza de caliper y montaje de balatas', 120.00, 2],
            [3, 'Revision Sistema Electrico', 'Diagnostico de bateria, estator y regulador de voltaje', 200.00, 3],
        ] as [$id, $nombre, $descripcion, $precio, $categoria]) {
            DB::table('servicios')->updateOrInsert(
                ['id_servicio' => $id],
                [
                    'ser_nombre' => $nombre,
                    'ser_descripcion' => $descripcion,
                    'ser_precio_mano_obra' => $precio,
                    'id_categoria' => $categoria,
                ],
            );
        }

        foreach ([
            [1, 'Administrador', 'General', 'Sistema', '5550001111', 'admin@tallermoto.com', 'Av. Principal 123', 'Administrador', '$2y$12$ER/PiQOfm6kl.oKWbE.uMO8op8Vhwu82LIqnbD6OMSob45wjM/gRu', 'Activo'],
            [2, 'Pedro', 'Martinez', 'Sanchez', '5552223333', 'pedro.vendedor@tallermoto.com', 'Calle 5 de Mayo #45', 'Vendedor', '$2y$12$2.GR6yrOYk4JdXb4RaKkg.ietgE.WxyRuAZqiunEfTM1.daT23St.', 'Activo'],
            [3, 'Jorge', 'Ramirez', 'Castro', '5554445555', 'jorge.mecanico@tallermoto.com', 'Col. Centro, Calle 3', 'Mecanico', '$2y$12$l/xLzQMB86uAtKpfNjhas./gM/ePJLmPgjx/DfpTRiCDSGcW1iTmq', 'Activo'],
            [4, 'Luis', 'Fernandez', 'Garcia', '5556667777', 'luis.mecanico@tallermoto.com', 'Av. Las Torres #89', 'Mecanico', '$2y$12$qyTTQqvXainvf9npSn9bOe2MNTRUaO3yOCQDPXk5vyDj.5Eye.wOW', 'Activo'],
        ] as [$id, $nombre, $apaterno, $amaterno, $telefono, $correo, $direccion, $rol, $password, $estado]) {
            DB::table('empleados')->updateOrInsert(
                ['id_empleados' => $id],
                [
                    'emp_nombre' => $nombre,
                    'emp_apaterno' => $apaterno,
                    'emp_amaterno' => $amaterno,
                    'emp_telefono' => $telefono,
                    'emp_correo' => $correo,
                    'emp_direccion' => $direccion,
                    'emp_rol' => $rol,
                    'tipo_usuario' => stripos($rol, 'admin') !== false ? 1 : 2,
                    'es_mecanico' => stripos($rol, 'mecanico') !== false,
                    'emp_usuario' => $correo,
                    'emp_password' => $password,
                    'emp_estado' => $estado,
                ],
            );
        }

        foreach ([
            [1, 'Juan', 'Lopez', 'Hernandez', '5551112233', 'juan.cliente@email.com', 'Calle Primavera #123, Col. Centro', '$2y$12$gmXmspquQtBTgr1QdTEDsebKSLHQbD4j1Ow9b/iovdzGAJQF9kALC'],
            [2, 'Maria', 'Garcia', 'Rodriguez', '5554445566', 'maria.cliente@email.com', 'Av. Siempre Viva #456', '$2y$12$hash_aqui_para_cliente123'],
            [3, 'Carlos', 'Sanchez', 'Diaz', '5557778899', 'carlos.cliente@email.com', 'Blvd. Principal #789', '$2y$12$hash_aqui_para_cliente123'],
        ] as [$id, $nombre, $apaterno, $amaterno, $telefono, $correo, $direccion, $password]) {
            DB::table('clientes')->updateOrInsert(
                ['id_cliente' => $id],
                [
                    'cli_nombre' => $nombre,
                    'cli_apaterno' => $apaterno,
                    'cli_amaterno' => $amaterno,
                    'cli_telefono' => $telefono,
                    'cli_correo' => $correo,
                    'cli_direccion' => $direccion,
                    'cli_password' => $password,
                    'tipo_usuario' => 3,
                    'cli_estado' => 'Activo',
                ],
            );
        }
    }

    private function syncProductTables(): void
    {
        $products = [
            [1, 'PROD-001', 'Balatas Delanteras HD', 'Refaccion', 'Brembo', 'Balatas de alta duracion para scooter y motos de trabajo', 250.00, 20, 2, 1],
            [2, 'PROD-002', 'Aceite Sintetico 10W40 1L', 'Insumo', 'Motul', 'Aceite sintetico para motor de 4 tiempos', 180.00, 50, 1, 1],
            [3, 'PROD-003', 'Bujia Iridium CR7HIX', 'Refaccion', 'NGK', 'Bujia de alta eficiencia para mejor combustion', 140.00, 30, 3, 3],
            [4, 'PROD-004', 'Casco Certificado Certus', 'Accesorio', 'SHAFT', 'Casco integral con certificacion DOT', 1450.00, 5, 4, 2],
            [5, 'PROD-005', 'Kit de Arrastre Completo', 'Refaccion', 'Choho', 'Cadena, pinon y corona para moto 150cc', 420.00, 12, 1, 3],
        ];

        foreach ($products as [$id, $codigo, $nombre, $tipo, $marca, $descripcion, $precio, $stock, $categoria, $proveedor]) {
            if (Schema::hasTable('producto')) {
                DB::table('producto')->updateOrInsert(
                    ['id_producto' => $id],
                    [
                        'pro_codigo' => $codigo,
                        'pro_nombre' => $nombre,
                        'pro_tipo' => $tipo,
                        'pro_marca' => $marca,
                        'pro_descripcion' => $descripcion,
                        'pro_precio_venta' => $precio,
                        'pro_stock' => $stock,
                        'id_categoria' => $categoria,
                        'id_proveedor' => $proveedor,
                    ],
                );
            }

            if (Schema::hasTable('productos')) {
                DB::table('productos')->updateOrInsert(
                    ['id_producto' => $id],
                    [
                        'pro_codigo' => $codigo,
                        'pro_nombre' => $nombre,
                        'pro_tipo' => $tipo,
                        'pro_marca' => $marca,
                        'pro_descripcion' => $descripcion,
                        'pro_precio_venta' => $precio,
                        'pro_iva' => 0,
                        'pro_stock' => $stock,
                        'pro_categoria' => $this->categoryName($categoria),
                        'id_categoria' => $categoria,
                        'id_proveedor' => $proveedor,
                    ],
                );
            }
        }

        if (Schema::hasTable('inventarios')) {
            foreach ($products as [$id, $codigo, $nombre, $tipo, $marca, $descripcion, $precio, $stock, $categoria, $proveedor]) {
                DB::table('inventarios')->updateOrInsert(
                    ['codigo_producto' => $codigo],
                    [
                        'id_producto' => $id,
                        'nombre_producto' => $nombre,
                        'marca' => $marca,
                        'categoria' => $this->categoryName($categoria),
                        'stock' => $stock,
                        'precio_unitario' => $precio,
                        'iva' => 0,
                        'precio_total' => $precio,
                        'id_proveedor' => $proveedor,
                        'proveedor' => $this->providerName($proveedor),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            }
        }
    }

    private function categoryName(int $id): ?string
    {
        return DB::table('categorias')->where('id_categoria', $id)->value('cat_nombre');
    }

    private function providerName(int $id): ?string
    {
        return DB::table('proveedores')->where('id_proveedor', $id)->value('prov_nombre');
    }

    public function down(): void
    {
        //
    }
};
