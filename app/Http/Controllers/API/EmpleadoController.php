<?php

namespace App\Http\Controllers\API;

use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmpleadoController extends Controller
{
    // LISTAR TODOS
    public function index()
    {
        return response()->json(Empleado::all(), 200);
    }

    // CREAR
    public function store(Request $request)
    {
        $empleado = Empleado::create($request->all());

        return response()->json([
            'message' => 'Empleado creado',
            'data' => $empleado
        ], 201);
    }

    // MOSTRAR UNO
    public function show($id)
    {
        return response()->json(
            Empleado::findOrFail($id)
        );
    }

    // ACTUALIZAR
    public function update(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);
        $empleado->update($request->all());

        return response()->json([
            'message' => 'Empleado actualizado'
        ]);
    }

    // ELIMINAR
    public function destroy($id)
    {
        Empleado::destroy($id);

        return response()->json([
            'message' => 'Empleado eliminado'
        ]);
    }
}