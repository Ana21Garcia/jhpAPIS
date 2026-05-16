<?php

namespace App\Http\Controllers\API;

use App\Models\Servicios;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiciosController extends Controller
{
    // LISTAR TODOS LOS SERVICIOS
    public function index()
    {
      
        return response()->json(Servicios::all(), 200);
    }

    // CREAR UN NUEVO SERVICIO
    public function store(Request $request)
    {
        $servicio = Servicios::create($request->all());

        return response()->json([
            'message' => 'Servicio creado con éxito',
            'data' => $servicio
        ], 201);
    }

    // MOSTRAR UN SERVICIO ESPECÍFICO
    public function show($id)
    {
        return response()->json(
            Servicios::findOrFail($id)
        );
    }

    // ACTUALIZAR DATOS DEL SERVICIO
    public function update(Request $request, $id)
    {
        $servicio = Servicios::findOrFail($id);
        $servicio->update($request->all());

        return response()->json([
            'message' => 'Servicio actualizado correctamente',
            'data' => $servicio
        ], 200);
    }

    // ELIMINAR SERVICIO
    public function destroy($id)
    {
        Servicios::destroy($id);

        return response()->json([
            'message' => 'Servicio eliminado del catálogo'
        ], 200);
    }
}