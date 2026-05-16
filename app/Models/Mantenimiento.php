<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
       protected $table = 'Mantenimiento';
    
    
    protected $primaryKey = 'id_mantenimiento';

   
    public $timestamps = false; 

        protected $fillable = [
        'id_cliente', 'id_mecanico', 'id_cita', 'moto_modelo', 
        'moto_llegada_descripcion', 'trabajo_realizado', 'fecha_inicio', 
        'fecha_termino', 'mantenimiento_total', 'estado_servicio'
    ];
}