<?php

namespace App\Http\Controllers\API;

use App\Models\Proveedor;
use App\Support\EnsureCatalogTables;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        DB::transaction(function () use ($id) {
            if (Schema::hasTable('compras') && Schema::hasColumn('compras', 'id_proveedor')) {
                DB::table('compras')->where('id_proveedor', $id)->update(['id_proveedor' => null]);
            }

            if (Schema::hasTable('productos') && Schema::hasColumn('productos', 'id_proveedor')) {
                $productUpdate = ['id_proveedor' => null];
                if (Schema::hasColumn('productos', 'pro_proveedor')) {
                    $productUpdate['pro_proveedor'] = null;
                }

                DB::table('productos')->where('id_proveedor', $id)->update($productUpdate);
            }

            if (Schema::hasTable('inventarios') && Schema::hasColumn('inventarios', 'id_proveedor')) {
                $inventoryUpdate = ['id_proveedor' => null];
                if (Schema::hasColumn('inventarios', 'proveedor')) {
                    $inventoryUpdate['proveedor'] = null;
                }

                DB::table('inventarios')
                    ->where('id_proveedor', $id)
                    ->update($inventoryUpdate);
            }

            Proveedor::findOrFail($id)->delete();
        });

        return response()->json([
            'message' => 'El proveedor se elimino correctamente'
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
