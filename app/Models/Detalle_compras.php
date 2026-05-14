<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detalle_compras extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'Detalle_Compras';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_det_compra';

    /**
     * Timestamps desactivados.
     */
    public $timestamps = false;

    /**
     * Campos habilitados para asignación masiva.
     */
    protected $fillable = [
        'id_compra',
        'id_producto',
        'det_cantidad',
        'det_costo_unitario',
    ];

    /**
     * Relaciones
     */
    public function compra()
    {
        return $this->belongsTo(Compras::class, 'id_compra', 'id_compra');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
