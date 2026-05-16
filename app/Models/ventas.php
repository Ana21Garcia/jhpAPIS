<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ventas extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'Ventas';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_venta';

   
    public $timestamps = false;

    
    protected $fillable = [
        'id_cliente',
        'id_empleado',
        'id_caja',
        'ven_fecha',
        'ven_total',
        'tipo_pago',
    ];

    /**
     * Relaciones
     */
    public function cliente() {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function empleado() {
        return $this->belongsTo(Empleados::class, 'id_empleado', 'id_empleados');
    }

    public function caja() {
        return $this->belongsTo(Control_Caja::class, 'id_caja', 'id_caja');
    }

    public function detalles() {
        return $this->hasMany(Detalle_ventas::class, 'id_venta', 'id_venta');
    }
}
