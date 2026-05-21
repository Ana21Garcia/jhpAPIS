<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Support\EnsureCatalogTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class EmpleadosController extends Controller
{
    public function index()
    {
        EnsureCatalogTables::ensure();

        return response()->json(Empleado::all(), 200);
    }

    public function store(Request $request)
    {
        EnsureCatalogTables::ensure();
        $data = $this->normalizarEmpleado($request->all());

        if (isset($data['emp_password'])) {
            $data['emp_password'] = Hash::make($data['emp_password']);
        }

        $empleado = Empleado::create($data);
        $this->syncCliente($empleado);

        return response()->json([
            'message' => 'Empleado registrado con exito',
            'data' => $empleado,
        ], 201);
    }

    public function show($id)
    {
        return response()->json(Empleado::findOrFail($id), 200);
    }

    public function update(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);
        $data = $this->normalizarEmpleado($request->all());

        if (!empty($data['emp_password'])) {
            $data['emp_password'] = Hash::make($data['emp_password']);
        } else {
            unset($data['emp_password']);
        }

        $empleado->update($data);
        $this->syncCliente($empleado->fresh());

        return response()->json([
            'message' => 'Datos del empleado actualizados correctamente',
            'data' => $empleado->fresh(),
        ], 200);
    }

    public function destroy($id)
    {
        Empleado::destroy($id);

        return response()->json([
            'message' => 'Empleado eliminado del sistema',
        ], 200);
    }

    private function normalizarEmpleado(array $data): array
    {
        if (isset($data['emp_apellido_paterno']) && !isset($data['emp_apaterno'])) {
            $data['emp_apaterno'] = $data['emp_apellido_paterno'];
        }

        if (isset($data['emp_apellido_materno']) && !isset($data['emp_amaterno'])) {
            $data['emp_amaterno'] = $data['emp_apellido_materno'];
        }

        if (isset($data['emp_email']) && !isset($data['emp_correo'])) {
            $data['emp_correo'] = $data['emp_email'];
        }

        if (isset($data['emp_correo']) && !isset($data['emp_usuario'])) {
            $data['emp_usuario'] = $data['emp_correo'];
        }

        if (isset($data['emp_usuario']) && !isset($data['emp_correo'])) {
            $data['emp_correo'] = $data['emp_usuario'];
        }

        $rolOriginal = (string) ($data['emp_rol'] ?? '');
        $rolNormalizado = strtolower($rolOriginal);

        if ($rolNormalizado !== '') {
            if (str_contains($rolNormalizado, 'cliente')) {
                $data['emp_rol'] = 'Vendedor';
            } elseif (str_contains($rolNormalizado, 'mecanico')) {
                $data['emp_rol'] = 'Mecanico';
            } elseif (str_contains($rolNormalizado, 'admin')) {
                $data['emp_rol'] = 'Administrador';
            } elseif (str_contains($rolNormalizado, 'empleado') || str_contains($rolNormalizado, 'vendedor')) {
                $data['emp_rol'] = 'Vendedor';
            }
        }

        if ($rolNormalizado !== '' && str_contains($rolNormalizado, 'cliente')) {
            $data['tipo_usuario'] = 3;
        } elseif ($rolNormalizado !== '' && str_contains($rolNormalizado, 'admin')) {
            $data['tipo_usuario'] = 1;
        } elseif (isset($data['tipo_usuario'])) {
            $data['tipo_usuario'] = (int) $data['tipo_usuario'];
        } else {
            $data['tipo_usuario'] = str_contains($rolNormalizado, 'admin') ? 1 : 2;
        }

        if ($rolNormalizado !== '') {
            $data['es_mecanico'] = str_contains($rolNormalizado, 'mecanico');
        }

        if (isset($data['es_mecanico'])) {
            $data['es_mecanico'] = filter_var($data['es_mecanico'], FILTER_VALIDATE_BOOLEAN);
        }

        unset($data['emp_apellido_paterno'], $data['emp_apellido_materno'], $data['emp_email']);

        return array_filter(
            $data,
            fn ($value, $key) => Schema::hasColumn('empleados', $key),
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function syncCliente(Empleado $empleado): void
    {
        if ((int) $empleado->tipo_usuario !== 3) {
            return;
        }

        Cliente::updateOrCreate(
            ['cli_correo' => $empleado->emp_correo ?? $empleado->emp_usuario],
            [
                'cli_nombre' => $empleado->emp_nombre,
                'cli_apaterno' => $empleado->emp_apaterno ?? 'Cliente',
                'cli_amaterno' => $empleado->emp_amaterno,
                'cli_telefono' => $empleado->emp_telefono,
                'cli_correo' => $empleado->emp_correo ?? $empleado->emp_usuario,
                'cli_direccion' => $empleado->emp_direccion,
                'cli_password' => $empleado->emp_password,
                'tipo_usuario' => 3,
                'cli_estado' => $empleado->emp_estado ?? 'Activo',
            ],
        );
    }
}
