<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'Clientes';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_cliente';

   
    public $timestamps = false;

   
    protected $fillable = [
        'cli_nombre',
        'cli_apaterno',
        'cli_amaterno',
        'cli_telefono',
        'cli_correo',
        'cli_fecha_registro',
    ];
}
