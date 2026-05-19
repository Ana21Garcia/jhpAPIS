<?php

namespace App\Http\Controllers\API;

use App\Models\Producto;
use App\Support\EnsureCatalogTables;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductoController extends Controller
{
    // LISTAR TODOS LOS PRODUCTOS
    public function index()
    {
        EnsureCatalogTables::ensure();
      
        $productos = Producto::with(['categoria', 'proveedor'])->get();
        return response()->json($productos, 200);
    }


    public function store(Request $request)
    {
        EnsureCatalogTables::ensure();
        $producto = Producto::create($this->normalizarProducto($request->all()));

        return response()->json([
            'message' => 'Producto registrado exitosamente',
            'data' => $producto
        ], 201);
    }


    public function show($id)
    {
        $producto = Producto::with(['categoria', 'proveedor'])->findOrFail($id);
        return response()->json($producto, 200);
    }

   
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->update($this->normalizarProducto($request->all()));

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

    private function normalizarProducto(array $data): array
    {
        return [
            'pro_codigo' => $data['pro_codigo'] ?? $data['prod_codigo'] ?? null,
            'pro_nombre' => $data['pro_nombre'] ?? $data['prod_nombre'] ?? null,
            'pro_tipo' => $data['pro_tipo'] ?? $data['prod_tipo'] ?? null,
            'pro_marca' => $data['pro_marca'] ?? $data['prod_marca'] ?? null,
            'pro_descripcion' => $data['pro_descripcion'] ?? $data['prod_descripcion'] ?? null,
            'pro_precio_venta' => $data['pro_precio_venta'] ?? $data['prod_precio'] ?? null,
            'pro_stock' => $data['pro_stock'] ?? $data['prod_stock'] ?? null,
            'id_categoria' => $data['id_categoria'] ?? null,
            'id_proveedor' => $data['id_proveedor'] ?? null,
        ];
    }
}
