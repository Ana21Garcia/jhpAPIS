<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordReset extends Model
{
    protected $table = 'password_resets';
    protected $primaryKey = 'id_reset';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'token',
        'correo',
        'fecha_solicitud',
        'fecha_expiracion',
        'utilizado',
        'fecha_uso',
        'ip_solicitud',
        'user_agent',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_expiracion' => 'datetime',
        'fecha_uso' => 'datetime',
        'utilizado' => 'boolean',
    ];

    /**
     * Relación con Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Generar token único de recuperación
     */
    public static function generateToken()
    {
        return hash('sha256', Str::random(60));
    }

    /**
     * Crear solicitud de recuperación
     */
    public static function crearSolicitud($idUsuario, $correo, $request = null)
    {
        self::where('correo', $correo)
            ->where('utilizado', false)
            ->update([
                'utilizado' => true,
                'fecha_uso' => now(),
            ]);

        $token = self::generateToken();
        $horasExpiracion = 24; // Token válido por 24 horas

        return self::create([
            'id_usuario' => $idUsuario,
            'token' => $token,
            'correo' => $correo,
            'fecha_solicitud' => now(),
            'fecha_expiracion' => now()->addHours($horasExpiracion),
            'ip_solicitud' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
        ]);
    }

    /**
     * Validar si el token es válido
     */
    public function esValido()
    {
        return !$this->utilizado && now()->lessThanOrEqualTo($this->fecha_expiracion);
    }

    /**
     * Marcar token como utilizado
     */
    public function marcarUtilizado()
    {
        $this->update([
            'utilizado' => true,
            'fecha_uso' => now(),
        ]);
    }

    /**
     * Buscar token válido
     */
    public static function obtenerPorToken($token)
    {
        return self::where('token', $token)
            ->where('utilizado', false)
            ->where('fecha_expiracion', '>', now())
            ->first();
    }
}
