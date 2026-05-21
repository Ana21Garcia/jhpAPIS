<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Control_caja;
use App\Models\Detalle_ventas;
use App\Models\Ventas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VentasController extends Controller
{
    public function index()
    {
        return response()->json(Ventas::with(['cliente', 'empleado', 'caja', 'detalles.producto'])->get(), 200);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $detalles = $request->input('detalles', []);

            foreach ($detalles as $prod) {
                InventarioController::descontarStock(
                    (int) $prod['id_producto'],
                    (int) ($prod['cantidad'] ?? $prod['det_cantidad'] ?? 0),
                );
            }

            $cajaAbierta = Control_caja::where('estado', 'Abierta')
                ->latest('id_caja')
                ->first();
            $idCaja = $this->resolverIdCajaValido($request->id_caja ?? $cajaAbierta?->id_caja);

            $venta = Ventas::create([
                'id_cliente'  => $request->id_cliente,
                'id_empleado' => $request->id_empleado,
                'id_caja'     => $idCaja,
                'ven_total'   => $request->ven_total,
                'tipo_pago'   => $request->tipo_pago,
                'ven_fecha'   => now(),
            ]);

            foreach ($detalles as $prod) {
                Detalle_ventas::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $prod['id_producto'],
                    'det_cantidad' => $prod['cantidad'] ?? $prod['det_cantidad'],
                    'det_precio_unitario' => $prod['precio'] ?? $prod['det_precio_unitario'],
                ]);
            }

            return response()->json([
                'message' => 'Venta registrada con exito',
                'id_venta' => $venta->id_venta,
            ], 201);
        });
    }

    public function show($id)
    {
        return response()->json(Ventas::with(['cliente', 'empleado', 'caja', 'detalles.producto'])->findOrFail($id), 200);
    }

    public function update(Request $request, $id)
    {
        $venta = Ventas::findOrFail($id);
        $venta->update($request->all());

        return response()->json([
            'message' => 'Venta actualizada correctamente',
            'data' => $venta,
        ], 200);
    }

    public function destroy($id)
    {
        $venta = Ventas::findOrFail($id);
        $venta->detalles()->delete();
        $venta->delete();

        return response()->json(['message' => 'Venta eliminada con exito'], 200);
    }

    private function resolverIdCajaValido($idCaja): ?int
    {
        if (!$idCaja) {
            return null;
        }

        $idCaja = (int) $idCaja;

        if (Schema::hasTable('control_caja')) {
            return DB::table('control_caja')->where('id_caja', $idCaja)->exists()
                ? $idCaja
                : null;
        }

        if (Schema::hasTable('control_cajas')) {
            return DB::table('control_cajas')->where('id_caja', $idCaja)->exists()
                ? $idCaja
                : null;
        }

        return null;
    }
}
