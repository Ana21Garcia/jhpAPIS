<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table = 'inventarios';

    protected $primaryKey = 'id_inventario';

    protected $fillable = [
        'id_producto',
        'codigo_producto',
        'nombre_producto',
        'marca',
        'categoria',
        'stock',
        'precio_unitario',
        'iva',
        'precio_total',
        'id_proveedor',
        'proveedor',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
