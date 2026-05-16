<?php

namespace App\Http\Controllers\API;

use App\Models\Ventas;
use Illuminate\Http\Request;
use App\Models\Detalle_ventas;
use App\Http\Controllers\Controller;

class VentasController extends Controller
{
    // LISTAR TODAS LAS VENTAS
    public function index()
    {
     
        $ventas = Ventas::with(['cliente', 'empleado', 'caja'])->get();
        return response()->json($ventas, 200);
    }

    // REGISTRAR 
public function store(Request $request)
{
    // 1. Crear 
    $venta = Ventas::create([
        'id_cliente'  => $request->id_cliente,
        'id_empleado' => $request->id_empleado ?? 1,
        'id_caja'     => $request->id_caja ?? 1,
        'ven_total'   => $request->ven_total,
        'tipo_pago'   => $request->tipo_pago,
        'ven_fecha'   => now(),
    ]);

    // 2. Guardar 
    if ($request->has('detalles') && is_array($request->detalles)) {
        foreach ($request->detalles as $prod) {
            // Detalle_ventas directamente
            Detalle_ventas::create([
                'id_venta'            => $venta->id_venta, // Vinculamos con el ID recién creado
                'id_producto'         => $prod['id_producto'],
                'det_cantidad'        => $prod['cantidad'],
                'det_precio_unitario' => $prod['precio'],
            ]);
        }
    }

    return response()->json([
        'message' => 'Venta registrada con éxito',
        'id_venta' => $venta->id_venta
    ], 201);
}

        // MOSTRAR UNA VENTA ESPECÍFICA CON SUS DETALLES DE PRODUCTOS
        public function show($id)
        {
            // Aquí incluimos 'detalles.producto' para ver exactamente qué artículos se vendieron
            $venta = Ventas::with(['cliente', 'empleado', 'caja', 'detalles.producto'])->findOrFail($id);
            return response()->json($venta, 200);
        }

    // ACTUALIZAR UNA VENTA 
    public function update(Request $request, $id)
    {
        $venta = Ventas::findOrFail($id);
        $venta->update($request->all());

        return response()->json([
            'message' => 'Venta actualizada correctamente',
            'data' => $venta
        ], 200);
    }

    // ELIMINAR REGISTRO DE VENTA
  public function destroy($id)
{
    try {
        $venta = Ventas::findOrFail($id);

        // Paso 1: Eliminar manualmente los detalles vinculados
       
        $venta->detalles()->delete();

        // Paso 2: Eliminar la venta
        $venta->delete();

        return response()->json(['message' => 'Venta eliminada con éxito'], 200);
    } catch (\Exception $e) {
      
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}