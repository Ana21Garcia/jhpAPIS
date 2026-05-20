<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'productos';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_producto';

    
    public $timestamps = false;

   
    protected $fillable = [
        'pro_codigo',
        'pro_nombre',
        'pro_tipo',
        'pro_marca',
        'pro_descripcion',
        'pro_precio_venta',
        'pro_iva',
        'pro_stock',
        'pro_categoria',
        'pro_proveedor',
        'id_categoria',
        'id_proveedor',
    ];

    
    public function categoria()
    {
        return $this->belongsTo(Categorias::class, 'id_categoria', 'id_categoria');
    }

  
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }
}
