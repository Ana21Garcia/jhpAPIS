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

    /**
     * En este caso, dado que usas un TIMESTAMP por defecto en SQL, 
     * desactivamos el manejo automático de Laravel.
     */
    public $timestamps = false;

    /**
     * Campos habilitados para asignación masiva.
     */
    protected $fillable = [
        'cli_nombre',
        'cli_apaterno',
        'cli_amaterno',
        'cli_telefono',
        'cli_correo',
        'cli_fecha_registro',
    ];
}
