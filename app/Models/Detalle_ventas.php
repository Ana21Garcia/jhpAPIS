<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detalle_ventas extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'Detalle_Ventas';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_detalle';

    /**
     * Desactivamos timestamps estándar.
     */
    public $timestamps = false;

    /**
     * Campos habilitados para asignación masiva.
     */
    protected $fillable = [
        'id_venta',
        'id_producto',
        'det_cantidad',
        'det_precio_unitario',
    ];

    /**
     * Relaciones
     */
    public function venta() {
        return $this->belongsTo(Ventas::class, 'id_venta', 'id_venta');
    }

    public function producto() {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
