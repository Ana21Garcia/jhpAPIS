<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compras extends Model
{
   
    protected $table = 'Compras';

   
    protected $primaryKey = 'id_compra';

    public $timestamps = false;

 
    protected $fillable = [
        'id_proveedor',
        'id_empleado',
        'com_fecha',
        'com_total',
        'com_factura_no',
    ];

    
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
