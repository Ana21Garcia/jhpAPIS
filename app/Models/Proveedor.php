<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
   
    protected $table = 'proveedores';

    
    protected $primaryKey = 'id';

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
