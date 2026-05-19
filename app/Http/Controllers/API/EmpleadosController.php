<?php

namespace App\Http\Controllers\API;

use App\Models\Empleado;
use App\Support\EnsureCatalogTables;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class EmpleadosController extends Controller
{
    // LISTAR TODOS LOS EMPLEADOS
    public function index()
    {
        EnsureCatalogTables::ensure();
       
        return response()->json(Empleado::all(), 200);
    }

    // CREAR UN NUEVO EMPLEADO
    public function store(Request $request)
    {
        $data = $request->all();
        
        // Encriptar la contraseña antes de guardar
        if (isset($data['emp_password'])) {
            $data['emp_password'] = Hash::make($data['emp_password']);
        }

        $empleado = Empleado::create($data);

        return response()->json([
            'message' => 'Empleado registrado con éxito',
            'data' => $empleado
        ], 201);
    }

    // MOSTRAR UN EMPLEADO POR ID
    public function show($id)
    {
        return response()->json(
            Empleado::findOrFail($id)
        );
    }

    // ACTUALIZAR DATOS DEL EMPLEADO
    public function update(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);
        $data = $request->all();

        // Si se envía una nueva contraseña, encriptarla
        if (!empty($data['emp_password'])) {
            $data['emp_password'] = Hash::make($data['emp_password']);
        } else {
            // Si no se envía contraseña, removerla del array para no sobreescribir con vacío
            unset($data['emp_password']);
        }

        $empleado->update($data);

        return response()->json([
            'message' => 'Datos del empleado actualizados correctamente',
            'data' => $empleado
        ], 200);
    }

    // ELIMINAR O DAR DE BAJA
    public function destroy($id)
    {
      
        Empleado::destroy($id);

        return response()->json([
            'message' => 'Empleado eliminado del sistema'
        ], 200);
    }
}
