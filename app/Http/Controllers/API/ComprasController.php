<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Compras;
use Illuminate\Http\Request;

class ComprasController extends Controller
{
    public function index()
    {
        return response()->json(Compras::with(['proveedor', 'empleado', 'detalles'])->get(), 200);
    }

    public function store(Request $request)
    {
        $compra = Compras::create($request->all());
        $items = $request->input('detalles', $request->input('productos', []));

        if (empty($items) && ($request->filled('codigo_producto') || $request->filled('productCode'))) {
            $items = [[
                'codigo_producto' => $request->input('codigo_producto', $request->input('productCode')),
                'nombre_producto' => $request->input('nombre_producto', $request->input('productName')),
                'marca' => $request->input('marca', $request->input('brand')),
                'categoria' => $request->input('categoria'),
                'stock' => $request->input('cantidad', $request->input('quantity', 0)),
                'precio_unitario' => $request->input('precio_unitario', $request->input('unitCost', 0)),
                'iva' => $request->input('iva', 0),
                'proveedor' => $request->input('proveedor', $request->input('provider')),
                'id_proveedor' => $request->input('id_proveedor'),
                'id_producto' => $request->input('id_producto'),
            ]];
        }

        foreach ($items as $item) {
            InventarioController::sumarOActualizar($item);
        }

        return response()->json([
            'message' => 'Compra registrada correctamente',
            'data' => $compra,
        ], 201);
    }

    public function show($id)
    {
        return response()->json(Compras::with(['proveedor', 'empleado', 'detalles'])->findOrFail($id), 200);
    }

    public function update(Request $request, $id)
    {
        $compra = Compras::findOrFail($id);
        $compra->update($request->all());

        return response()->json([
            'message' => 'Informacion de la compra actualizada',
            'data' => $compra,
        ], 200);
    }

    public function destroy($id)
    {
        Compras::destroy($id);

        return response()->json(['message' => 'Registro de compra eliminado'], 200);
    }
}
