<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Citas;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CitasController extends Controller
{
    public function index()
    {
        $citas = Citas::with(['cliente', 'empleado'])->get();
        return response()->json($citas, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_cliente' => 'nullable|integer|exists:clientes,id_cliente',
            'id_empleado' => 'nullable|integer|exists:empleados,id_empleados',
            'cita_fecha_programada' => 'required|date',
            'cita_motivo' => 'nullable|string|max:255',
            'cita_estado' => 'nullable|in:Pendiente,Confirmada,Cancelada,Realizada',
            'cita_notas' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'No se pudo guardar la cita. Revisa cliente, empleado, fecha y estado.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $cita = Citas::create($validator->validated());
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'No se pudo guardar la cita por una restriccion de la base de datos.',
                'error' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Cita programada con exito',
            'data' => $cita,
        ], 201);
    }

    public function show($id)
    {
        $cita = Citas::with(['cliente', 'empleado'])->findOrFail($id);
        return response()->json($cita, 200);
    }

    public function update(Request $request, $id)
    {
        $cita = Citas::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_cliente' => 'sometimes|nullable|integer|exists:clientes,id_cliente',
            'id_empleado' => 'sometimes|nullable|integer|exists:empleados,id_empleados',
            'cita_fecha_programada' => 'sometimes|required|date',
            'cita_motivo' => 'sometimes|nullable|string|max:255',
            'cita_estado' => 'sometimes|nullable|in:Pendiente,Confirmada,Cancelada,Realizada',
            'cita_notas' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'No se pudo actualizar la cita. Revisa cliente, empleado, fecha y estado.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $cita->update($validator->validated());
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'No se pudo actualizar la cita por una restriccion de la base de datos.',
                'error' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Cita actualizada correctamente',
            'data' => $cita,
        ], 200);
    }

    public function destroy($id)
    {
        Citas::destroy($id);

        return response()->json([
            'message' => 'Cita eliminada del sistema',
        ], 200);
    }
}
