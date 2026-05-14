<?php

namespace App\Http\Controllers\API;

use App\Models\Detalle_cita_servicios;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Detalle_cita_serviciosController extends Controller
{
    // LISTAR TODOS LOS DETALLES
    public function index()
    {
        return response()->json(Detalle_cita_servicios::all(), 200);
    }

    // VINCULAR UN SERVICIO A UNA CITA
    public function store(Request $request)
    {
        $detalle = Detalle_cita_servicios::create($request->all());

        return response()->json([
            'message' => 'Servicio asignado a la cita con éxito',
            'data' => $detalle
        ], 201);
    }

    // MOSTRAR UN DETALLE ESPECÍFICO
    public function show($id)
    {
        return response()->json(
            Detalle_cita_servicios::findOrFail($id)
        );
    }

    // ACTUALIZAR EL DETALLE (Por ejemplo, cambiar el servicio asignado)
    public function update(Request $request, $id)
    {
        $detalle = Detalle_cita_servicios::findOrFail($id);
        $detalle->update($request->all());

        return response()->json([
            'message' => 'Detalle de cita actualizado',
            'data' => $detalle
        ], 200);
    }

    // ELIMINAR UN SERVICIO DE LA CITA
    public function destroy($id)
    {
        Detalle_cita_servicios::destroy($id);

        return response()->json([
            'message' => 'Servicio removido de la cita'
        ], 200);
    }
}