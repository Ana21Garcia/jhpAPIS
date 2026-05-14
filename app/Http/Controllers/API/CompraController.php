<?php

namespace App\Http\Controllers\API;

use App\Models\Compra;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompraController extends Controller
{
    // LISTAR TODOS
    public function index()
    {
        return response()->json(Compra::all(), 200);
    }

    // CREAR
    public function store(Request $request)
    {
        $compras = Compra::create($request->all());

        return response()->json([
            'message' => 'compra creada',
            'data' => $compras
        ], 201);
    }

    // MOSTRAR UNO
    public function show($id)
    {
        return response()->json(
            Compra::findOrFail($id)
        );
    }

    // ACTUALIZAR
    public function update(Request $request, $id)
    {
        $compras = Compra::findOrFail($id);
        $compras->update($request->all());

        return response()->json([
            'message' => 'compra actualizada'
        ]);
    }

    // ELIMINAR
    public function destroy($id)
    {
        Compra::destroy($id);

        return response()->json([
            'message' => 'compra eliminada'
        ]);
    }
}