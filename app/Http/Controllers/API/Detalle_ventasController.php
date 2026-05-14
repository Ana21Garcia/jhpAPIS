<?php

namespace App\Http\Controllers\API;

use App\Models\Detalle_ventas;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Detalle_ventasController extends Controller
{
    // LISTAR TODOS LOS DETALLES DE VENTAS
    public function index()
    {
        // Cargamos la relación con el producto para saber qué se vendió
        $detalles = Detalle_ventas::with(['producto', 'venta'])->get();
        return response()->json($detalles, 200);
    }

    // REGISTRAR UN PRODUCTO EN UNA VENTA
    public function store(Request $request)
    {
        $detalle = Detalle_ventas::create($request->all());

        return response()->json([
            'message' => 'Producto agregado a la venta correctamente',
            'data' => $detalle
        ], 201);
    }

    // MOSTRAR UN DETALLE ESPECÍFICO
    public function show($id)
    {
        $detalle = Detalle_ventas::with(['producto', 'venta'])->findOrFail($id);
        return response()->json($detalle, 200);
    }

    // ACTUALIZAR CANTIDAD O PRECIO DE UN ÍTEM VENDIDO
    public function update(Request $request, $id)
    {
        $detalle = Detalle_ventas::findOrFail($id);
        $detalle->update($request->all());

        return response()->json([
            'message' => 'Línea de venta actualizada',
            'data' => $detalle
        ], 200);
    }

    // ELIMINAR UN PRODUCTO DE LA VENTA
    public function destroy($id)
    {
        Detalle_ventas::destroy($id);

        return response()->json([
            'message' => 'Producto eliminado de la venta'
        ], 200);
    }
}