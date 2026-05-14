<?php

namespace App\Http\Controllers\API;

use App\Models\Producto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductoController extends Controller
{
    // LISTAR TODOS LOS PRODUCTOS
    public function index()
    {
        // Cargamos categoría y proveedor para facilitar la vista en el frontend
        $productos = Producto::with(['categoria', 'proveedor'])->get();
        return response()->json($productos, 200);
    }

    // REGISTRAR UN NUEVO PRODUCTO
    public function store(Request $request)
    {
        $producto = Producto::create($request->all());

        return response()->json([
            'message' => 'Producto registrado exitosamente',
            'data' => $producto
        ], 201);
    }

    // MOSTRAR UN PRODUCTO ESPECÍFICO
    public function show($id)
    {
        $producto = Producto::with(['categoria', 'proveedor'])->findOrFail($id);
        return response()->json($producto, 200);
    }

    // ACTUALIZAR DATOS DEL PRODUCTO O STOCK
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->update($request->all());

        return response()->json([
            'message' => 'Producto actualizado correctamente',
            'data' => $producto
        ], 200);
    }

    // ELIMINAR PRODUCTO
    public function destroy($id)
    {
        Producto::destroy($id);

        return response()->json([
            'message' => 'Producto eliminado del inventario'
        ], 200);
    }
}