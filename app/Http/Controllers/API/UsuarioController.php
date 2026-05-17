<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    /**
     * Obtener todos los usuarios (solo admin)
     * GET /api/usuarios
     */
    public function index(Request $request)
    {
        try {
            $usuario = $request->user();

            if (!$usuario || $usuario->tipo_usuario !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado',
                ], 403);
            }

            $usuarios = Usuario::paginate(15);

            return response()->json([
                'success' => true,
                'data' => $usuarios,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener un usuario específico
     * GET /api/usuarios/{id}
     */
    public function show(Request $request, $id)
    {
        try {
            $usuario = $request->user();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado',
                ], 401);
            }

            // Los usuarios solo pueden ver su propia información o si son admin
            if ($usuario->id_usuario != $id && $usuario->tipo_usuario !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado',
                ], 403);
            }

            $usuarioBuscado = Usuario::find($id);

            if (!$usuarioBuscado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $usuarioBuscado,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear nuevo usuario (solo admin)
     * POST /api/usuarios
     */
    public function store(Request $request)
    {
        try {
            $usuario = $request->user();

            if (!$usuario || $usuario->tipo_usuario !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'correo' => 'required|email|unique:usuarios',
                'password' => 'required|string|min:8',
                'tipo_usuario' => 'required|in:Empleado,Admin,Cliente',
                'estado' => 'required|in:Activo,Inactivo,Bloqueado',
                'id_empleado' => 'nullable|integer',
                'id_cliente' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $nuevoUsuario = Usuario::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => $nuevoUsuario,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar usuario
     * PUT /api/usuarios/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $usuario = $request->user();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado',
                ], 401);
            }

            // Los usuarios solo pueden actualizar su propia información o si son admin
            if ($usuario->id_usuario != $id && $usuario->tipo_usuario !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado',
                ], 403);
            }

            $usuarioBuscado = Usuario::find($id);

            if (!$usuarioBuscado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'correo' => 'email|unique:usuarios,correo,' . $id . ',id_usuario',
                'tipo_usuario' => 'in:Empleado,Admin,Cliente',
                'estado' => 'in:Activo,Inactivo,Bloqueado',
                'id_empleado' => 'nullable|integer',
                'id_cliente' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $usuarioBuscado->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => $usuarioBuscado,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar usuario (solo admin)
     * DELETE /api/usuarios/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $usuario = $request->user();

            if (!$usuario || $usuario->tipo_usuario !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado',
                ], 403);
            }

            $usuarioBuscado = Usuario::find($id);

            if (!$usuarioBuscado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            $usuarioBuscado->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cambiar estado de usuario (solo admin)
     * PATCH /api/usuarios/{id}/estado
     */
    public function cambiarEstado(Request $request, $id)
    {
        try {
            $usuario = $request->user();

            if (!$usuario || $usuario->tipo_usuario !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'estado' => 'required|in:Activo,Inactivo,Bloqueado',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $usuarioBuscado = Usuario::find($id);

            if (!$usuarioBuscado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            $usuarioBuscado->update(['estado' => $request->estado]);

            return response()->json([
                'success' => true,
                'message' => 'Estado del usuario actualizado',
                'data' => $usuarioBuscado,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
