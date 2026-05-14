<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    // Nombre exacto de la tabla en tu SQL
    protected $table = 'Mantenimiento';
    
    // Llave primaria personalizada
    protected $primaryKey = 'id_mantenimiento';

    // IMPORTANTE: Tu SQL no tiene created_at ni updated_at
    public $timestamps = false; 

    // Permitir asignación masiva para estos campos
    protected $fillable = [
        'id_cliente', 'id_mecanico', 'id_cita', 'moto_modelo', 
        'moto_llegada_descripcion', 'trabajo_realizado', 'fecha_inicio', 
        'fecha_termino', 'mantenimiento_total', 'estado_servicio'
    ];
}