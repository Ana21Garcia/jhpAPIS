<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
   
    protected $table = 'proveedores';

    
    protected $primaryKey = 'id_proveedor';

    public $timestamps = false;

    /**
     * Campos.
     */
    protected $fillable = [
        'prov_nombre',
        'prov_contacto',
        'prov_telefono',
        'prov_email',
        'prov_direccion',
    ];
}
