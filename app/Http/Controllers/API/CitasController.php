<?php

namespace App\Http\Controllers\API;

use App\Models\Citas;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CitasController extends Controller
{
    // LISTAR TODAS LAS CITAS (Incluyendo datos de cliente y empleado)
    public function index()
    {
        $citas = Citas::with(['cliente', 'empleado'])->get();
        return response()->json($citas, 200);
    }

    // CREAR UNA NUEVA CITA
    public function store(Request $request)
    {
        $cita = Citas::create($request->all());

        return response()->json([
            'message' => 'Cita programada con éxito',
            'data' => $cita
        ], 201);
    }

    // MOSTRAR UNA CITA ESPECÍFICA
    public function show($id)
    {
        $cita = Citas::with(['cliente', 'empleado'])->findOrFail($id);
        return response()->json($cita, 200);
    }

    // ACTUALIZAR CITA (Ideal para cambiar el estado o la fecha)
    public function update(Request $request, $id)
    {
        $cita = Citas::findOrFail($id);
        $cita->update($request->all());

        return response()->json([
            'message' => 'Cita actualizada correctamente',
            'data' => $cita
        ], 200);
    }

    // ELIMINAR O CANCELAR CITA
    public function destroy($id)
    {
        Citas::destroy($id);

        return response()->json([
            'message' => 'Cita eliminada del sistema'
        ], 200);
    }
}