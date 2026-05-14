<?php

namespace App\Http\Controllers\API;

use App\Models\Detalle_mantenimiento_servicios;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Detalle_mantenimiento_serviciosController extends Controller
{
    // LISTAR TODOS LOS SERVICIOS REALIZADOS EN MANTENIMIENTOS
    public function index()
    {
        return response()->json(Detalle_mantenimiento_servicios::all(), 200);
    }

    // REGISTRAR UN SERVICIO DENTRO DE UN MANTENIMIENTO
    public function store(Request $request)
    {
        $detalle = Detalle_mantenimiento_servicios::create($request->all());

        return response()->json([
            'message' => 'Servicio registrado en el mantenimiento con éxito',
            'data' => $detalle
        ], 201);
    }

    // MOSTRAR UN DETALLE ESPECÍFICO
    public function show($id)
    {
        return response()->json(
            Detalle_mantenimiento_servicios::findOrFail($id)
        );
    }

    // ACTUALIZAR PRECIO O CAMBIAR EL SERVICIO
    public function update(Request $request, $id)
    {
        $detalle = Detalle_mantenimiento_servicios::findOrFail($id);
        $detalle->update($request->all());

        return response()->json([
            'message' => 'Detalle de servicio actualizado',
            'data' => $detalle
        ], 200);
    }

    // ELIMINAR EL SERVICIO DEL MANTENIMIENTO
    public function destroy($id)
    {
        Detalle_mantenimiento_servicios::destroy($id);

        return response()->json([
            'message' => 'Servicio removido del registro de mantenimiento'
        ], 200);
    }
}