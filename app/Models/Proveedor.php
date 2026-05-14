<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    /**
     * Tabla del  modelo.
     */
    protected $table = 'proveedores';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id';

    /**
     * 
     */
    public $timestamps = false;

    /**
     * Campos.
     */
    protected $fillable = [
        'nombre',
        'telefono',
        'correo',
        'marca',
        'ultima_visita',
    ];
}
