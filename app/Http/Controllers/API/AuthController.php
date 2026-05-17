<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Login de usuario
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'correo' => 'required|email',
                'password' => 'required|string|min:6',
            ], [
                'correo.required' => 'El correo es requerido',
                'correo.email' => 'El correo debe ser válido',
                'password.required' => 'La contraseña es requerida',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $usuario = $this->findAuthUser($request->correo);
            $userFallback = null;

            if ($usuario && ! $this->verifyPassword($usuario, $request->password)) {
                // Si existe una cuenta en usuarios pero no coincide la contraseña,
                // intentamos autenticar con la tabla users como respaldo.
                $userFallback = User::where('email', $request->correo)->first();
                if ($userFallback && $this->verifyPassword($userFallback, $request->password)) {
                    $usuario = $userFallback;
                }
            }

            if (!$usuario || ! $this->verifyPassword($usuario, $request->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Correo o contraseña inválidos',
                ], 401);
            }

            // Actualizar último acceso solo para usuarios de la tabla usuarios
            if ($usuario instanceof Usuario) {
                $usuario->updateUltimoAcceso();
            }

            // Generar token (usando Laravel Sanctum)
            $token = $usuario->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'data' => [
                    'usuario' => $usuario,
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar usuario para login en usuarios o users
     */
    private function findAuthUser(string $correo)
    {
        $usuario = Usuario::where('correo', $correo)
            ->where('estado', 'Activo')
            ->first();

        if ($usuario) {
            return $usuario;
        }

        return User::where('email', $correo)->first();
    }

    /**
     * Verificar contraseña y migrar texto plano a hash si es necesario.
     */
    private function verifyPassword($usuario, string $password): bool
    {
        $stored = $usuario->password;

        // Si el valor almacenado parece un hash válido, use Hash::check.
        if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2b$') || str_starts_with($stored, '$argon2')) {
            try {
                return Hash::check($password, $stored);
            } catch (\Exception $e) {
                // En caso de hash inválido, continuamos con la comparación en texto plano.
            }
        }

        // Soporte para contraseñas en texto plano existentes en la base de datos.
        if ($stored === $password) {
            $usuario->password = Hash::make($password);
            $usuario->save();
            return true;
        }

        return false;
    }

    /**
     * Logout de usuario
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        try {
            if ($request->user()) {
                $request->user()->currentAccessToken()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Cierre de sesión exitoso',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener información del usuario autenticado
     * GET /api/auth/me
     */
    public function me(Request $request)
    {
        try {
            $usuario = $request->user();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => $usuario,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Registrar nuevo usuario (opcional - para clientes)
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'correo' => 'required|email|unique:usuarios',
                'password' => 'required|string|min:8|confirmed',
                'nombre' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $usuario = Usuario::create([
                'correo' => $request->correo,
                'password' => $request->password,
                'tipo_usuario' => 'Cliente',
                'estado' => 'Activo',
            ]);

            $token = $usuario->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'usuario' => $usuario,
                    'token' => $token,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
