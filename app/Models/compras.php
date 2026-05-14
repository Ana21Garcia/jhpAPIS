<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compras extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'Compras';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_compra';

    /**
     * Timestamps desactivados.
     */
    public $timestamps = false;

    /**
     * Campos habilitados para asignación masiva.
     */
    protected $fillable = [
        'id_proveedor',
        'id_empleado',
        'com_fecha',
        'com_total',
        'com_factura_no',
    ];

    /**
     * Relaciones
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedores::class, 'id_proveedor', 'id_proveedor');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleados::class, 'id_empleado', 'id_empleados');
    }

    public function detalles()
    {
        return $this->hasMany(Detalle_compras::class, 'id_compra', 'id_compra');
    }
}
