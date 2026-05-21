<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'correo' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validacion',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $resultadoAuth = $this->findAuthUser($request->correo);

            if (!$resultadoAuth || !$this->verifyPassword($resultadoAuth['usuario'], $request->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales invalidas',
                ], 401);
            }

            $usuario = $resultadoAuth['usuario'];
            $tipoUsuario = $resultadoAuth['tipo'];
            $rol = 'Cliente';
            $nombreCompleto = $usuario->nombre_completo ?? $usuario->cli_nombre ?? $usuario->name ?? 'Usuario';
            $userId = $usuario->id_cliente ?? $usuario->id;
            $correo = $usuario->cli_correo ?? $usuario->email;

            if ($tipoUsuario === 'empleado') {
                $rol = $usuario->emp_rol;
                $nombreCompleto = $usuario->nombre_completo ?: $usuario->emp_nombre;
                $userId = $usuario->id_empleados;
                $correo = $usuario->emp_correo ?: $usuario->emp_usuario;
            } elseif ($tipoUsuario === 'user') {
                $rol = 'Admin';
                $nombreCompleto = $usuario->name;
                $userId = $usuario->id;
                $correo = $usuario->email;
            }

            $token = $usuario->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesion exitoso',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'usuario' => [
                        'id' => $userId,
                        'name' => $nombreCompleto,
                        'nombre' => $nombreCompleto,
                        'correo' => $correo,
                        'rol' => $rol,
                        'tipo' => $tipoUsuario,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function findAuthUser(string $correo): ?array
    {
        $user = User::where('email', $correo)->first();

        if ($user) {
            return ['usuario' => $user, 'tipo' => 'user'];
        }

        $empleado = Empleado::where(function ($query) use ($correo) {
            $query->where('emp_correo', $correo);
            if (Schema::hasColumn('empleados', 'emp_usuario')) {
                $query->orWhere('emp_usuario', $correo);
            }
        })
            ->where('emp_estado', 'Activo')
            ->first();

        if ($empleado) {
            return ['usuario' => $empleado, 'tipo' => 'empleado'];
        }

        $cliente = Cliente::where('cli_correo', $correo)->first();

        if ($cliente && $cliente->isActivo()) {
            return ['usuario' => $cliente, 'tipo' => 'cliente'];
        }

        return null;
    }

    private function verifyPassword($usuario, string $password): bool
    {
        $stored = $usuario->emp_password ?? $usuario->cli_password ?? $usuario->password ?? null;

        if (!$stored) {
            return false;
        }

        if (password_get_info($stored)['algo'] > 0) {
            return Hash::check($password, $stored);
        }

        if ($stored === $password) {
            $campo = isset($usuario->emp_password)
                ? 'emp_password'
                : (isset($usuario->cli_password) ? 'cli_password' : 'password');
            $usuario->$campo = Hash::make($password);
            $usuario->save();
            return true;
        }

        return false;
    }

    public function logout(Request $request)
    {
        try {
            if ($request->user() && $request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Sesion cerrada exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesion',
            ], 500);
        }
    }

    public function me(Request $request)
    {
        $usuario = $request->user();

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
            ], 401);
        }

        $nombre = $usuario->nombre_completo
            ?? $usuario->name
            ?? $usuario->emp_nombre
            ?? $usuario->cli_nombre
            ?? 'Usuario';

        return response()->json([
            'success' => true,
            'data' => [
                'usuario' => $usuario,
                'name' => $nombre,
                'nombre' => $nombre,
            ],
        ], 200);
    }

    public function register(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'El registro publico esta deshabilitado. Usa el modulo de empleados/clientes.',
        ], 403);
    }
}
