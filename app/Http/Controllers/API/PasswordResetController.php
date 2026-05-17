<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Mail\PasswordResetMail;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Solicitar recuperación de contraseña
     * POST /api/password-reset/request
     */
    public function requestReset(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'correo' => 'required|email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $usuario = $this->findPasswordResetUser($request->correo);

            // No revelar si el correo existe o no (seguridad)
            if (!$usuario) {
                return response()->json([
                    'success' => true,
                    'message' => 'Si el correo está registrado, recibirá instrucciones de recuperación',
                ], 200);
            }

            // Crear solicitud de recuperación
            $passwordReset = PasswordReset::crearSolicitud(
                $usuario instanceof Usuario ? $usuario->id_usuario : null,
                $usuario instanceof Usuario ? $usuario->correo : $usuario->email,
                $request
            );

            // Enviar email con token
            $mailSent = true;
            $mailError = null;

            try {
                Mail::to($usuario instanceof Usuario ? $usuario->correo : $usuario->email)
                    ->send(new PasswordResetMail($usuario, $passwordReset->token));
            } catch (\Exception $mailException) {
                $mailSent = false;
                $mailError = $mailException->getMessage();
                \Log::error('Error enviando email de recuperación: ' . $mailError);
            }

            $response = [
                'success' => true,
                'message' => 'Si el correo está registrado, recibirá instrucciones de recuperación',
                'debug_info' => [
                    'token_created' => true,
                    'token_expires_in_hours' => 24,
                    'mail_sent' => $mailSent,
                ],
            ];

            if (!$mailSent) {
                $response['debug_info']['mail_error'] = $mailError;
            }

            if (config('app.debug')) {
                $response['debug_info']['token'] = $passwordReset->token;
                $response['debug_info']['correo'] = $usuario instanceof Usuario ? $usuario->correo : $usuario->email;
            }

            return response()->json($response, 200);

        } catch (\Exception $e) {
            \Log::error('Error en solicitud de recuperación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validar token de recuperación
     * POST /api/password-reset/validate-token
     */
    public function validateToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $passwordReset = PasswordReset::obtenerPorToken($request->token);

            if (!$passwordReset) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido o expirado',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Token válido',
                'data' => [
                    'correo' => $passwordReset->correo,
                    'fecha_expiracion' => $passwordReset->fecha_expiracion,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resetear contraseña
     * POST /api/password-reset/reset
     */
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ], [
                'password.confirmed' => 'Las contraseñas no coinciden',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $passwordReset = PasswordReset::obtenerPorToken($request->token);

            if (!$passwordReset) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido o expirado',
                ], 400);
            }

            // Validar que no sea expirado
            if (!$passwordReset->esValido()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El token ha expirado',
                ], 400);
            }

            // Obtener usuarios por correo: preferimos la tabla `users` pero
            // también actualizaremos `usuarios` si existe.
            $userQuery = User::where('email', $passwordReset->correo);
            if (Schema::hasColumn('users', 'correo')) {
                $userQuery->orWhere('correo', $passwordReset->correo);
            }
            $userModel = $userQuery->first();
            $usuarioModel = $passwordReset->usuario; // may be null

            if (!$userModel && !$usuarioModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            // Actualizar contraseña
            try {
                // Actualizar directamente la tabla `users` (hash explícito)
                $usersQuery = DB::table('users')->where('email', $passwordReset->correo);
                if (Schema::hasColumn('users', 'correo')) {
                    $usersQuery->orWhere('correo', $passwordReset->correo);
                }
                $updatedUsers = $usersQuery->update(['password' => Hash::make($request->password)]);

                // También actualizar vía modelo `User` si está disponible (usa mutator)
                if ($userModel instanceof \App\Models\User) {
                    $userModel->password = $request->password; // mutator aplicará hashing
                    $userModel->save();
                    // Verificar que la contraseña quedó hasheada en la DB; si no, forzar actualización
                    $fresh = $userModel->fresh();
                    if (is_null($fresh) || (!str_starts_with($fresh->password, '$2y$') && !str_starts_with($fresh->password, '$argon'))) {
                        try {
                            $usersQueryForce = DB::table('users')->where('email', $passwordReset->correo);
                            if (Schema::hasColumn('users', 'correo')) {
                                $usersQueryForce->orWhere('correo', $passwordReset->correo);
                            }
                            $usersQueryForce->update(['password' => Hash::make($request->password)]);
                        } catch (\Exception $e) {
                            \Log::error('Error forzando hash en users: ' . $e->getMessage());
                        }
                    }
                }

                // Luego actualizar la tabla `usuarios` si existe (mutator en Usuario)
                if ($usuarioModel instanceof \App\Models\Usuario) {
                    $usuarioModel->password = $request->password;
                    $usuarioModel->save();
                }
            } catch (\Exception $e) {
                \Log::error('Error actualizando contraseña: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error actualizando la contraseña',
                ], 500);
            }

            // Marcar token como utilizado
            $passwordReset->marcarUtilizado();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar usuario para recuperación en usuarios o users
     */
    private function findPasswordResetUser(string $correo)
    {
        $usuario = Usuario::where('correo', $correo)->first();

        if ($usuario) {
            return $usuario;
        }

        $userQuery = User::where('email', $correo);
        if (Schema::hasColumn('users', 'correo')) {
            $userQuery->orWhere('correo', $correo);
        }

        return $userQuery->first();
    }

    /**
     * Cambiar contraseña (usuario autenticado)
     * POST /api/password-reset/change
     */
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password_actual' => 'required|string',
                'password_nueva' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $usuario = $request->user();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado',
                ], 401);
            }

            // Verificar contraseña actual
            if (!Hash::check($request->password_actual, $usuario->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta',
                ], 400);
            }

            // Actualizar contraseña
            $usuario->update([
                'password' => Hash::make($request->password_nueva),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
