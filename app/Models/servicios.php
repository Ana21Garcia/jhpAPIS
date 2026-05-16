<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicios extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'Servicios';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_servicio';

   
    public $timestamps = false;

    
    protected $fillable = [
        'ser_nombre',
        'ser_descripcion',
        'ser_precio_mano_obra',
        'id_categoria',
    ];
}
