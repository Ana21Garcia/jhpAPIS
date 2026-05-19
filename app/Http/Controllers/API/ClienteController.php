<?php

namespace App\Http\Controllers\API;

use App\Models\Cliente;
use App\Support\EnsureCatalogTables;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClienteController extends Controller
{
    // LISTAR TODOS LOS CLIENTES
    public function index()
    {
        EnsureCatalogTables::ensure();
        return response()->json(Cliente::all(), 200);
    }

    // CREAR UN NUEVO CLIENTE
    public function store(Request $request)
    {
        $cliente = Cliente::create($request->all());

        return response()->json([
            'message' => 'Cliente registrado con éxito',
            'data' => $cliente
        ], 201);
    }

    // MOSTRAR UN CLIENTE ESPECÍFICO
    public function show($id)
    {
        return response()->json(
            Cliente::findOrFail($id)
        );
    }

    // ACTUALIZAR DATOS DEL CLIENTE
    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->update($request->all());

        return response()->json([
            'message' => 'Datos del cliente actualizados',
            'data' => $cliente
        ], 200);
    }

    // ELIMINAR CLIENTE
    public function destroy($id)
    {
        Cliente::destroy($id);

        return response()->json([
            'message' => 'Cliente eliminado del sistema'
        ], 200);
    }
}
