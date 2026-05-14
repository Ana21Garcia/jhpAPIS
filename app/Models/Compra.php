<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compras';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'producto_id',
        'marca',
        'modelo',
        'precio',
        'cantidad',
        'fecha',
        'proveedor_id',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}
