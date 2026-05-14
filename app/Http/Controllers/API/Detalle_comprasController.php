<?php

namespace App\Http\Controllers\API;

use App\Models\Detalle_compras;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Detalle_comprasController extends Controller
{
    // LISTAR TODOS LOS DETALLES DE COMPRAS
    public function index()
    {
        // Cargamos la relación con el producto para saber qué se compró
        $detalles = Detalle_compras::with('producto')->get();
        return response()->json($detalles, 200);
    }

    // REGISTRAR UN PRODUCTO EN UNA COMPRA
    public function store(Request $request)
    {
        $detalle = Detalle_compras::create($request->all());

        return response()->json([
            'message' => 'Producto agregado al detalle de compra',
            'data' => $detalle
        ], 201);
    }

    // MOSTRAR UN DETALLE ESPECÍFICO
    public function show($id)
    {
        $detalle = Detalle_compras::with(['compra', 'producto'])->findOrFail($id);
        return response()->json($detalle, 200);
    }

    // ACTUALIZAR CANTIDAD O COSTO
    public function update(Request $request, $id)
    {
        $detalle = Detalle_compras::findOrFail($id);
        $detalle->update($request->all());

        return response()->json([
            'message' => 'Detalle de compra actualizado',
            'data' => $detalle
        ], 200);
    }

    // ELIMINAR UN PRODUCTO DEL DETALLE
    public function destroy($id)
    {
        Detalle_compras::destroy($id);

        return response()->json([
            'message' => 'Producto eliminado del detalle de compra'
        ], 200);
    }
}