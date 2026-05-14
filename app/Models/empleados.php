<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleados extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'Empleados';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_empleados';

    /**
     * No se utilizan timestamps automáticos.
     */
    public $timestamps = false;

    /**
     * Campos ocultos (útil para la contraseña al convertir a JSON).
     */
    protected $hidden = [
        'emp_password',
    ];

    /**
     * Campos habilitados para asignación masiva.
     */
    protected $fillable = [
        'emp_nombre',
        'emp_apaterno',
        'emp_amaterno',
        'emp_telefono',
        'emp_direccion',
        'emp_rol',
        'emp_usuario',
        'emp_password',
        'emp_estado',
    ];
}
