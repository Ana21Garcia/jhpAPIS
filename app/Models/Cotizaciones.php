<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizaciones extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'Cotizaciones';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_cotizacion';

 
    public $timestamps = false;

    
    protected $fillable = [
        'id_cliente',
        'id_empleado',
        'cot_fecha',
        'cot_vigencia_dias',
        'cot_total',
    ];

   
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

        public function empleado()
    {
        return $this->belongsTo(Empleados::class, 'id_empleado', 'id_empleados');
    }

  
public function detalles()
{
    // Una cotización TIENE MUCHOS detalles
    return $this->hasMany(Detalle_cotizaciones::class, 'id_cotizacion', 'id_cotizacion');
}

}
