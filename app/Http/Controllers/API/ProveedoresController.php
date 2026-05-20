<?php

namespace App\Http\Controllers\API;

use App\Models\Proveedor;
use App\Support\EnsureCatalogTables;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProveedoresController extends Controller
{
    // LISTAR TODOS LOS PROVEEDORES
    public function index()
    {
        EnsureCatalogTables::ensure();
        return response()->json(Proveedor::with('productos')->get(), 200);
    }

    // REGISTRAR UN NUEVO PROVEEDOR
    public function store(Request $request)
    {
        EnsureCatalogTables::ensure();
        $proveedor = Proveedor::create($this->normalizarProveedor($request->all()));

        return response()->json([
            'message' => 'Proveedor registrado con éxito',
            'data' => $proveedor
        ], 201);
    }

    // MOSTRAR UN PROVEEDOR ESPECÍFICO
    public function show($id)
    {
        return response()->json(
            Proveedor::with('productos')->findOrFail($id)
        );
    }

    // ACTUALIZAR DATOS DEL PROVEEDOR
    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->update($this->normalizarProveedor($request->all()));

        return response()->json([
            'message' => 'Información del proveedor actualizada',
            'data' => $proveedor
        ], 200);
    }

    // ELIMINAR PROVEEDOR
    public function destroy($id)
    {
        Proveedor::destroy($id);

        return response()->json([
            'message' => 'Proveedor eliminado del sistema'
        ], 200);
    }

    private function normalizarProveedor(array $data): array
    {
        if (isset($data['productos_sucursal']) && is_string($data['productos_sucursal'])) {
            $data['productos_sucursal'] = array_values(array_filter(array_map('trim', explode(',', $data['productos_sucursal']))));
        }

        return $data;
    }
}
