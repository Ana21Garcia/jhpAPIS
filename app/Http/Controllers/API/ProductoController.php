<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Support\EnsureCatalogTables;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        EnsureCatalogTables::ensure();

        return response()->json(Producto::with(['categoria', 'proveedor'])->get(), 200);
    }

    public function store(Request $request)
    {
        EnsureCatalogTables::ensure();
        $producto = Producto::create($this->normalizarProducto($request->all()));
        $this->syncInventario($producto, (int) $producto->pro_stock);

        return response()->json([
            'message' => 'Producto registrado exitosamente',
            'data' => $producto->fresh(['categoria', 'proveedor']),
        ], 201);
    }

    public function show($id)
    {
        return response()->json(Producto::with(['categoria', 'proveedor'])->findOrFail($id), 200);
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->update($this->normalizarProducto($request->all()));
        $this->syncInventario($producto, 0);

        return response()->json([
            'message' => 'Producto actualizado correctamente',
            'data' => $producto->fresh(['categoria', 'proveedor']),
        ], 200);
    }

    public function destroy($id)
    {
        Producto::destroy($id);

        return response()->json(['message' => 'Producto eliminado del inventario'], 200);
    }

    private function normalizarProducto(array $data): array
    {
        $precio = (float) ($data['pro_precio_venta'] ?? $data['prod_precio'] ?? 0);
        $iva = (float) ($data['pro_iva'] ?? $data['prod_iva'] ?? $data['iva'] ?? 0);

        return [
            'pro_codigo' => $data['pro_codigo'] ?? $data['prod_codigo'] ?? null,
            'pro_nombre' => $data['pro_nombre'] ?? $data['prod_nombre'] ?? null,
            'pro_tipo' => $data['pro_tipo'] ?? $data['prod_tipo'] ?? null,
            'pro_marca' => $data['pro_marca'] ?? $data['prod_marca'] ?? null,
            'pro_descripcion' => $data['pro_descripcion'] ?? $data['prod_descripcion'] ?? null,
            'pro_precio_venta' => $precio,
            'pro_iva' => $iva,
            'pro_stock' => $data['pro_stock'] ?? $data['prod_stock'] ?? 0,
            'pro_categoria' => $data['pro_categoria'] ?? $data['prod_categoria'] ?? null,
            'pro_proveedor' => $data['pro_proveedor'] ?? $data['prod_proveedor'] ?? null,
            'id_categoria' => $data['id_categoria'] ?? null,
            'id_proveedor' => $data['id_proveedor'] ?? null,
        ];
    }

    private function syncInventario(Producto $producto, int $cantidad): void
    {
        InventarioController::sumarOActualizar([
            'id_producto' => $producto->id_producto,
            'codigo_producto' => $producto->pro_codigo,
            'nombre_producto' => $producto->pro_nombre,
            'marca' => $producto->pro_marca,
            'categoria' => $producto->pro_categoria,
            'stock' => $cantidad,
            'precio_unitario' => (float) $producto->pro_precio_venta,
            'iva' => (float) ($producto->pro_iva ?? 0),
            'id_proveedor' => $producto->id_proveedor,
            'proveedor' => $producto->pro_proveedor,
        ]);
    }
}
