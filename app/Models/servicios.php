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

    /**
     * Desactivar timestamps si la tabla no tiene created_at y updated_at.
     */
    public $timestamps = false;

    /**
     * Campos habilitados para asignación masiva.
     */
    protected $fillable = [
        'ser_nombre',
        'ser_descripcion',
        'ser_precio_mano_obra',
        'id_categoria',
    ];
}
