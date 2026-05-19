<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    
    protected $table = 'empleados';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_empleados';

    /**
     *
     */
    public $timestamps = false;

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

    /**
     * Ocultar el password cuando se convierta a array/JSON.
     */
    protected $hidden = [
        'emp_password',
    ];
}
