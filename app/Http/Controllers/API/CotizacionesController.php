<?php

namespace App\Http\Controllers\API;

use App\Models\Cotizaciones;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CotizacionesController extends Controller
{
    // 1. LISTAR: Ahora incluimos detalles y productos para que la descripción no salga vacía
    public function index()
    {
        $cotizaciones = Cotizaciones::with(['cliente', 'empleado', 'detalles.producto', 'detalles.servicio'])->get();
        $cotizaciones->each(function ($cotizacion) {
            $fecha = $cotizacion->cot_fecha ? \Carbon\Carbon::parse($cotizacion->cot_fecha) : now();
            $vence = $fecha->copy()->addDays((int) ($cotizacion->cot_vigencia_dias ?? 0));
            $cotizacion->cot_estado = now()->lte($vence) ? 'Vigente' : 'Vencida';
        });
        return response()->json($cotizaciones, 200);
    }

    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                foreach ($request->input('detalles', []) as $item) {
                    if ((int) ($item['det_cantidad'] ?? 0) <= 0) {
                        return response()->json(['message' => 'La cotizacion requiere piezas mayores a 0'], 422);
                    }
                    if (empty($item['id_producto']) && empty($item['id_servicio'])) {
                        return response()->json(['message' => 'Cada detalle requiere un producto o servicio registrado'], 422);
                    }
                }

                $cotizacion = Cotizaciones::create([
                    'id_cliente'        => $request->id_cliente,
                    'id_empleado'       => $request->id_empleado,
                    'cot_fecha'         => now(),
                    'cot_vigencia_dias' => $request->cot_vigencia_dias ?? 15,
                    'cot_estado'         => 'Vigente',
                    'cot_total'         => $request->cot_total,
                ]);

                if ($request->has('detalles')) {
                    foreach ($request->detalles as $item) {
                        $cotizacion->detalles()->create([
                            'id_producto'         => $item['id_producto'] ?? null,
                            'id_servicio'         => $item['id_servicio'] ?? null,
                            'det_cantidad'        => $item['det_cantidad'],
                            'det_precio_unitario' => $item['det_precio_unitario'],
                        ]);
                    }
                }
                return response()->json(['message' => 'Guardado con éxito'], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 2. ACTUALIZAR: Este es el método que te faltaba y causaba el error
    public function update(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $cotizacion = Cotizaciones::findOrFail($id);
                foreach ($request->input('detalles', []) as $item) {
                    if ((int) ($item['det_cantidad'] ?? 0) <= 0) {
                        return response()->json(['message' => 'La cotizacion requiere piezas mayores a 0'], 422);
                    }
                    if (empty($item['id_producto']) && empty($item['id_servicio'])) {
                        return response()->json(['message' => 'Cada detalle requiere un producto o servicio registrado'], 422);
                    }
                }
                
                $cotizacion->update([
                    'id_cliente'        => $request->id_cliente,
                    'cot_vigencia_dias' => $request->cot_vigencia_dias,
                    'cot_estado'         => 'Vigente',
                    'cot_total'         => $request->cot_total,
                ]);

                // Borramos detalles viejos y re-insertamos los nuevos
                $cotizacion->detalles()->delete();

                foreach ($request->detalles as $item) {
                    $cotizacion->detalles()->create([
                        'id_producto'         => $item['id_producto'] ?? null,
                        'id_servicio'         => $item['id_servicio'] ?? null,
                        'det_cantidad'        => $item['det_cantidad'],
                        'det_precio_unitario' => $item['det_precio_unitario'],
                    ]);
                }

                return response()->json(['message' => 'Actualizado correctamente'], 200);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $cotizacion = Cotizaciones::with(['cliente', 'empleado', 'detalles.producto', 'detalles.servicio'])->findOrFail($id);
        return response()->json($cotizacion);
    }

    public function destroy($id)
    {
        Cotizaciones::destroy($id);
        return response()->json(['message' => 'Cotización eliminada'], 200);
    }
}
