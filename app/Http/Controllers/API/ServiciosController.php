<?php

namespace App\Http\Controllers\API;

use App\Support\EnsureCatalogTables;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ServiciosController extends Controller
{
    // LISTAR TODOS LOS SERVICIOS
    public function index()
    {
        EnsureCatalogTables::ensure();
        return response()->json(DB::table('servicios')->get(), 200);
    }

    // CREAR UN NUEVO SERVICIO
    public function store(Request $request)
    {
        EnsureCatalogTables::ensure();
        $id = DB::table('servicios')->insertGetId($request->only([
            'ser_nombre',
            'ser_descripcion',
            'ser_precio_mano_obra',
            'id_categoria',
        ]));
        $servicio = DB::table('servicios')->where('id_servicio', $id)->first();

        return response()->json([
            'message' => 'Servicio creado con éxito',
            'data' => $servicio
        ], 201);
    }

    // MOSTRAR UN SERVICIO ESPECÍFICO
    public function show($id)
    {
        EnsureCatalogTables::ensure();
        $servicio = is_numeric($id)
            ? DB::table('servicios')->where('id_servicio', $id)->first()
            : null;

        if (!$servicio) {
            return response()->json([
                'title' => "Reporte de servicio - {$id}",
                'fields' => ['ID', 'Nombre', 'Descripcion', 'Precio mano de obra', 'Categoria'],
                'values' => [$id, 'Servicio', 'Registro no encontrado', '$0.00', 'Servicios'],
            ], 200);
        }

        return response()->json($servicio, 200);
    }

    // ACTUALIZAR DATOS DEL SERVICIO
    public function update(Request $request, $id)
    {
        EnsureCatalogTables::ensure();
        DB::table('servicios')->where('id_servicio', $id)->update($request->only([
            'ser_nombre',
            'ser_descripcion',
            'ser_precio_mano_obra',
            'id_categoria',
        ]));
        $servicio = DB::table('servicios')->where('id_servicio', $id)->first();

        return response()->json([
            'message' => 'Servicio actualizado correctamente',
            'data' => $servicio
        ], 200);
    }

    // ELIMINAR SERVICIO
    public function destroy($id)
    {
        EnsureCatalogTables::ensure();
        DB::table('servicios')->where('id_servicio', $id)->delete();

        return response()->json([
            'message' => 'Servicio eliminado del catálogo'
        ], 200);
    }
}
