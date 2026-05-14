<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detalle_cita_servicios extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'Detalle_Cita_Servicios';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_det_cita';

    /**
     * Desactivar timestamps.
     */
    public $timestamps = false;

    /**
     * Campos habilitados para asignación masiva.
     */
    protected $fillable = [
        'id_cita',
        'id_servicio',
    ];
}
