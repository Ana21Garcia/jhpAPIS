<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'Producto';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_producto';

    /**
     * No se utilizan timestamps automáticos.
     */
    public $timestamps = false;

    /**
     * Campos habilitados para asignación masiva.
     */
    protected $fillable = [
        'pro_codigo',
        'pro_nombre',
        'pro_tipo',
        'pro_marca',
        'pro_descripcion',
        'pro_precio_venta',
        'pro_stock',
        'id_categoria',
        'id_proveedor',
    ];

    /**
     * Relación con el modelo Categoria.
     */
    public function categoria()
    {
        return $this->belongsTo(Categorias::class, 'id_categoria', 'id_categoria');
    }

    /**
     * Relación con el modelo Proveedor.
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedores::class, 'id_proveedor', 'id_proveedor');
    }
}
