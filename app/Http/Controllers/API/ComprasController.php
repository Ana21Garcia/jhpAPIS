<?php

namespace App\Http\Controllers\API;

use App\Models\Compras;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ComprasController extends Controller
{
    // LISTAR TODAS LAS COMPRAS
    public function index()
    {
        // Cargamos las relaciones para tener la información completa
        $compras = Compras::with(['proveedor', 'empleado', 'detalles'])->get();
        return response()->json($compras, 200);
    }

    // REGISTRAR UNA COMPRA
    public function store(Request $request)
    {
        $compra = Compras::create($request->all());

        return response()->json([
            'message' => 'Compra registrada correctamente',
            'data' => $compra
        ], 201);
    }

    // MOSTRAR UNA COMPRA ESPECÍFICA CON SUS DETALLES
    public function show($id)
    {
        $compra = Compras::with(['proveedor', 'empleado', 'detalles'])->findOrFail($id);
        return response()->json($compra, 200);
    }

    // ACTUALIZAR UNA COMPRA
    public function update(Request $request, $id)
    {
        $compra = Compras::findOrFail($id);
        $compra->update($request->all());

        return response()->json([
            'message' => 'Información de la compra actualizada',
            'data' => $compra
        ], 200);
    }

    // ELIMINAR REGISTRO DE COMPRA
    public function destroy($id)
    {
        Compras::destroy($id);

        return response()->json([
            'message' => 'Registro de compra eliminado'
        ], 200);
    }
}