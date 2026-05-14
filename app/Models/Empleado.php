<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    
    protected $table = 'empleados';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id';

    /**
     *
     */
    public $timestamps = false;

    /**
     * Campos asignables
     */
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'correo',
        'password',
        'rol',
    ];

    /**
     * Ocultar el password cuando se convierta a array/JSON.
     */
    protected $hidden = [
        'password',
    ];
}
