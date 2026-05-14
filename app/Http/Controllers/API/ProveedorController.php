<?php

namespace App\Http\Controllers\API;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProveedorController extends Controller
{
    // LISTAR TODOS
    public function index()
    {
        return response()->json(Proveedor::all(), 200);
    }

    // CREAR
    public function store(Request $request)
    {
        $proveedores = Proveedor::create($request->all());

        return response()->json([
            'message' => 'Proveedor creado',
            'data' => $proveedores
        ], 201);
    }

    // MOSTRAR UNO
    public function show($id)
    {
        return response()->json(
            Proveedor::findOrFail($id)
        );
    }

    // ACTUALIZAR
    public function update(Request $request, $id)
    {
        $proveedores = Proveedor::findOrFail($id);
        $proveedores->update($request->all());

        return response()->json([
            'message' => 'Proveedor actualizado'
        ]);
    }

    // ELIMINAR
    public function destroy($id)
    {
        Proveedor::destroy($id);

        return response()->json([
            'message' => 'Proveedor eliminado'
        ]);
    }
}