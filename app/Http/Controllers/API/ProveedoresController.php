<?php

namespace App\Http\Controllers\API;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProveedoresController extends Controller
{
    // LISTAR TODOS LOS PROVEEDORES
    public function index()
    {
        return response()->json(Proveedor::all(), 200);
    }

    // REGISTRAR UN NUEVO PROVEEDOR
    public function store(Request $request)
    {
        $proveedor = Proveedor::create($request->all());

        return response()->json([
            'message' => 'Proveedor registrado con éxito',
            'data' => $proveedor
        ], 201);
    }

    // MOSTRAR UN PROVEEDOR ESPECÍFICO
    public function show($id)
    {
        return response()->json(
            Proveedor::findOrFail($id)
        );
    }

    // ACTUALIZAR DATOS DEL PROVEEDOR
    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->update($request->all());

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
}
