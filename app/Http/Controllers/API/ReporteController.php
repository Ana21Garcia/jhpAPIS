<?php

namespace App\Http\Controllers\API; // Coincide con tu carpeta API

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ReporteController extends Controller
{
    public function dashboardGraficas(Request $request)
    {
        try {
            $dias = max(3, min((int) $request->query('dias', 7), 30));
            $inicio = Carbon::now()->subDays($dias - 1)->startOfDay();
            $fin = Carbon::now()->endOfDay();

            $ventas = DB::table('ventas')
                ->selectRaw('DATE(ven_fecha) as fecha')
                ->selectRaw('SUM(ven_total) as total')
                ->whereBetween('ven_fecha', [$inicio, $fin])
                ->groupBy('fecha')
                ->pluck('total', 'fecha');

            $compras = DB::table('compras')
                ->selectRaw('DATE(com_fecha) as fecha')
                ->selectRaw('SUM(com_total) as total')
                ->whereBetween('com_fecha', [$inicio, $fin])
                ->groupBy('fecha')
                ->pluck('total', 'fecha');

            $tendencia = [];
            for ($i = 0; $i < $dias; $i++) {
                $fecha = $inicio->copy()->addDays($i)->format('Y-m-d');
                $tendencia[] = [
                    'fecha' => $fecha,
                    'ventas' => (float) ($ventas[$fecha] ?? 0),
                    'compras' => (float) ($compras[$fecha] ?? 0),
                ];
            }

            $topProductos = DB::table('detalle_ventas')
                ->leftJoin('producto', 'detalle_ventas.id_producto', '=', 'producto.id_producto')
                ->leftJoin('productos', 'detalle_ventas.id_producto', '=', 'productos.id_producto')
                ->selectRaw("COALESCE(producto.pro_nombre, productos.pro_nombre, CONCAT('Producto ', detalle_ventas.id_producto)) as nombre")
                ->selectRaw('SUM(detalle_ventas.det_cantidad) as total_vendido')
                ->groupBy('nombre')
                ->orderByDesc('total_vendido')
                ->limit(5)
                ->get();

            $inventarioQuery = Schema::hasTable('inventarios')
                ? DB::table('inventarios')->selectRaw('nombre_producto as nombre, codigo_producto as codigo, stock')
                : DB::table('producto')->selectRaw('pro_nombre as nombre, pro_codigo as codigo, pro_stock as stock');

            $inventario = $inventarioQuery->get();
            $totalInventario = $inventario->count();
            $sinStock = $inventario->filter(fn ($item) => (int) $item->stock <= 0)->count();
            $stockBajo = $inventario->filter(fn ($item) => (int) $item->stock > 0 && (int) $item->stock <= 5)->count();
            $stockOk = max($totalInventario - $sinStock - $stockBajo, 0);
            $productosBajoStock = $inventario
                ->filter(fn ($item) => (int) $item->stock <= 5)
                ->sortBy('stock')
                ->take(10)
                ->values()
                ->map(fn ($item) => [
                    'codigo' => $item->codigo,
                    'nombre' => $item->nombre,
                    'stock' => (int) $item->stock,
                    'estado' => (int) $item->stock <= 0 ? 'SIN STOCK' : 'BAJO',
                ]);

            return response()->json([
                'tendencia' => $tendencia,
                'top_productos' => $topProductos,
                'inventario' => [
                    'total' => $totalInventario,
                    'ok' => $stockOk,
                    'bajo' => $stockBajo,
                    'sin_stock' => $sinStock,
                ],
                'bajo_stock' => $productosBajoStock,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Fallo en la consulta de graficas del dashboard',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }

    public function masSolicitados(Request $request)
    {
        try {
            $productosVentas = DB::table('detalle_ventas')
                ->leftJoin('productos', 'detalle_ventas.id_producto', '=', 'productos.id_producto')
                ->leftJoin('producto', 'detalle_ventas.id_producto', '=', 'producto.id_producto')
                ->selectRaw("COALESCE(productos.pro_nombre, producto.pro_nombre, CONCAT('Producto ', detalle_ventas.id_producto)) as nombre")
                ->selectRaw('SUM(detalle_ventas.det_cantidad) as solicitudes')
                ->selectRaw("'Producto' as tipo")
                ->groupBy('nombre');

            $productosMantenimiento = DB::table('detalle_mantenimiento_insumos')
                ->leftJoin('productos', 'detalle_mantenimiento_insumos.id_producto', '=', 'productos.id_producto')
                ->leftJoin('producto', 'detalle_mantenimiento_insumos.id_producto', '=', 'producto.id_producto')
                ->selectRaw("COALESCE(productos.pro_nombre, producto.pro_nombre, CONCAT('Producto ', detalle_mantenimiento_insumos.id_producto)) as nombre")
                ->selectRaw('SUM(detalle_mantenimiento_insumos.insumo_cantidad) as solicitudes')
                ->selectRaw("'Producto' as tipo")
                ->groupBy('nombre');

            $servicios = DB::table('detalle_mantenimiento_servicios')
                ->leftJoin('servicios', 'detalle_mantenimiento_servicios.id_servicio', '=', 'servicios.id_servicio')
                ->selectRaw("COALESCE(servicios.ser_nombre, CONCAT('Servicio ', detalle_mantenimiento_servicios.id_servicio)) as nombre")
                ->selectRaw('COUNT(*) as solicitudes')
                ->selectRaw("'Servicio' as tipo")
                ->groupBy('nombre');

            $mantenimientos = DB::table('mantenimiento')
                ->selectRaw("COALESCE(moto_modelo, 'Mantenimiento') as nombre")
                ->selectRaw('COUNT(*) as solicitudes')
                ->selectRaw("'Mantenimiento' as tipo")
                ->groupBy('nombre');

            $items = $productosVentas
                ->unionAll($productosMantenimiento)
                ->unionAll($servicios)
                ->unionAll($mantenimientos);

            $data = DB::query()
                ->fromSub($items, 'solicitudes')
                ->select('nombre', 'tipo')
                ->selectRaw('SUM(solicitudes) as solicitudes')
                ->groupBy('nombre', 'tipo')
                ->orderByDesc('solicitudes')
                ->limit((int) $request->query('limit', 15))
                ->get();

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Fallo en la consulta de mas solicitados',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }

    public function datosGraficas(Request $request)
    {
        try {
            $periodo = $request->query('periodo', 'semana');
            $fechaInicio = ($periodo === 'semana') ? Carbon::now()->startOfWeek() : Carbon::now()->startOfMonth();

            // vents
            $ventas = DB::table('Mantenimiento')
                ->select(
                    DB::raw('DATE(fecha_inicio) as fecha'), 
                    DB::raw('SUM(mantenimiento_total) as total')
                )
                ->where('estado_servicio', '=', 'Terminado')
                ->where('fecha_inicio', '>=', $fechaInicio)
                ->groupBy('fecha')
                ->orderBy('fecha', 'asc')
                ->get();

            // 2. Top 5 Productos
            $productos = DB::table('Detalle_Mantenimiento_Insumos')
                ->join('Producto', 'Detalle_Mantenimiento_Insumos.id_producto', '=', 'Producto.id_producto')
                ->select(
                    'Producto.pro_nombre as nombre', 
                    DB::raw('SUM(Detalle_Mantenimiento_Insumos.insumo_cantidad) as total_vendido')
                )
                ->groupBy('Producto.pro_nombre')
                ->orderBy('total_vendido', 'desc')
                ->limit(5)
                ->get();

            // 3. Totales 
            $manoObra = DB::table('Detalle_Mantenimiento_Servicios')
                ->join('Mantenimiento', 'Detalle_Mantenimiento_Servicios.id_mantenimiento', '=', 'Mantenimiento.id_mantenimiento')
                ->where('Mantenimiento.estado_servicio', '=', 'Terminado')
                ->where('Mantenimiento.fecha_inicio', '>=', $fechaInicio)
                ->sum('Detalle_Mantenimiento_Servicios.precio_aplicado') ?: 0;

            $ingresoTotal = DB::table('Mantenimiento')
                ->where('estado_servicio', '=', 'Terminado')
                ->where('fecha_inicio', '>=', $fechaInicio)
                ->sum('mantenimiento_total') ?: 0;

            return response()->json([
                'ventas' => $ventas,
                'productos' => $productos,
                'totales' => [
                    'ingreso_total' => (float)$ingresoTotal,
                    'mano_obra' => (float)$manoObra,
                    'refacciones' => (float)($ingresoTotal - $manoObra)
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Fallo en la consulta de reportes',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
