<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function index()
    {
        return response()->json(Inventario::with('producto')->orderBy('id_inventario')->get(), 200);
    }

    public function store(Request $request)
    {
        $data = $this->normalizar($request->all());
        $inventario = $this->sumarOActualizar($data);

        return response()->json([
            'message' => 'Inventario guardado correctamente',
            'data' => $inventario,
        ], 201);
    }

    public function show($id)
    {
        return response()->json(Inventario::with('producto')->findOrFail($id), 200);
    }

    public function update(Request $request, $id)
    {
        $inventario = Inventario::findOrFail($id);
        $data = $this->normalizar($request->all());
        $data['precio_total'] = ((float) ($data['precio_unitario'] ?? $inventario->precio_unitario)) + ((float) ($data['iva'] ?? $inventario->iva));

        if ($this->hasDuplicateInventory($data, (int) $id)) {
            return response()->json([
                'message' => 'Ya existe un producto de inventario con ese codigo, marca y categoria.',
            ], 422);
        }

        try {
            $inventario->update($data);
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return response()->json([
                    'message' => 'Ya existe un producto de inventario con ese codigo, marca y categoria.',
                ], 422);
            }

            throw $exception;
        }

        $this->syncProducto($inventario);

        return response()->json([
            'message' => 'Inventario actualizado correctamente',
            'data' => $inventario->fresh(),
        ], 200);
    }

    public function destroy($id)
    {
        Inventario::destroy($id);

        return response()->json(['message' => 'Registro de inventario eliminado'], 200);
    }

    public static function sumarOActualizar(array $data): Inventario
    {
        $codigo = $data['codigo_producto'] ?? $data['pro_codigo'] ?? null;
        $marca = $data['marca'] ?? $data['pro_marca'] ?? null;
        $categoria = $data['categoria'] ?? $data['pro_categoria'] ?? null;

        $inventario = Inventario::where('codigo_producto', $codigo)
            ->where('marca', $marca)
            ->where('categoria', $categoria)
            ->first();

        $cantidad = (int) ($data['stock'] ?? $data['cantidad'] ?? 0);
        $precio = (float) ($data['precio_unitario'] ?? $data['pro_precio_venta'] ?? 0);
        $iva = (float) ($data['iva'] ?? $data['pro_iva'] ?? 0);

        if ($inventario) {
            $inventario->fill([
                'nombre_producto' => $data['nombre_producto'] ?? $data['pro_nombre'] ?? $inventario->nombre_producto,
                'stock' => $inventario->stock + $cantidad,
                'precio_unitario' => $precio ?: $inventario->precio_unitario,
                'iva' => $iva,
                'precio_total' => ($precio ?: $inventario->precio_unitario) + $iva,
                'id_producto' => $data['id_producto'] ?? $inventario->id_producto,
                'id_proveedor' => $data['id_proveedor'] ?? $inventario->id_proveedor,
                'proveedor' => $data['proveedor'] ?? $inventario->proveedor,
            ])->save();
        } else {
            $inventario = Inventario::create([
                'id_producto' => $data['id_producto'] ?? null,
                'codigo_producto' => $codigo,
                'nombre_producto' => $data['nombre_producto'] ?? $data['pro_nombre'] ?? 'Producto',
                'marca' => $marca,
                'categoria' => $categoria,
                'stock' => $cantidad,
                'precio_unitario' => $precio,
                'iva' => $iva,
                'precio_total' => $precio + $iva,
                'id_proveedor' => $data['id_proveedor'] ?? null,
                'proveedor' => $data['proveedor'] ?? null,
            ]);
        }

        (new self())->syncProducto($inventario);
        return $inventario;
    }

    public static function descontarStock(int $idProducto, int $cantidad): void
    {
        $inventario = Inventario::where('id_producto', $idProducto)->lockForUpdate()->first();
        if (!$inventario) {
            $producto = Producto::find($idProducto);
            if ($producto) {
                $inventario = self::sumarOActualizar([
                    'id_producto' => $producto->id_producto,
                    'codigo_producto' => $producto->pro_codigo,
                    'nombre_producto' => $producto->pro_nombre,
                    'marca' => $producto->pro_marca,
                    'categoria' => $producto->pro_categoria,
                    'stock' => (int) $producto->pro_stock,
                    'precio_unitario' => (float) $producto->pro_precio_venta,
                    'iva' => (float) ($producto->pro_iva ?? 0),
                ]);
            }
        }

        if (!$inventario || $inventario->stock < $cantidad) {
            abort(response()->json([
                'message' => 'Stock insuficiente para vender este articulo',
                'stock_disponible' => $inventario?->stock ?? 0,
            ], 422));
        }

        $inventario->decrement('stock', $cantidad);
        (new self())->syncProducto($inventario->fresh());
    }

    private function normalizar(array $data): array
    {
        return [
            'id_producto' => $data['id_producto'] ?? null,
            'codigo_producto' => $data['codigo_producto'] ?? $data['pro_codigo'] ?? $data['prod_codigo'] ?? null,
            'nombre_producto' => $data['nombre_producto'] ?? $data['pro_nombre'] ?? $data['prod_nombre'] ?? null,
            'marca' => $data['marca'] ?? $data['pro_marca'] ?? $data['prod_marca'] ?? null,
            'categoria' => $data['categoria'] ?? $data['pro_categoria'] ?? $data['prod_categoria'] ?? null,
            'stock' => $data['stock'] ?? $data['cantidad'] ?? $data['pro_stock'] ?? $data['prod_stock'] ?? 0,
            'precio_unitario' => $data['precio_unitario'] ?? $data['pro_precio_venta'] ?? $data['prod_precio'] ?? 0,
            'iva' => $data['iva'] ?? $data['pro_iva'] ?? 0,
            'id_proveedor' => $data['id_proveedor'] ?? null,
            'proveedor' => $data['proveedor'] ?? $data['pro_proveedor'] ?? $data['prod_proveedor'] ?? null,
        ];
    }

    private function syncProducto(Inventario $inventario): void
    {
        if (!$inventario->id_producto) {
            return;
        }

        Producto::where('id_producto', $inventario->id_producto)->update([
            'pro_stock' => $inventario->stock,
            'pro_precio_venta' => $inventario->precio_unitario,
            'pro_iva' => $inventario->iva,
            'pro_categoria' => $inventario->categoria,
            'pro_proveedor' => $inventario->proveedor,
        ]);
    }

    private function hasDuplicateInventory(array $data, int $currentId): bool
    {
        return Inventario::where('codigo_producto', $data['codigo_producto'])
            ->where('marca', $data['marca'])
            ->where('categoria', $data['categoria'])
            ->where('id_inventario', '!=', $currentId)
            ->exists();
    }
}
