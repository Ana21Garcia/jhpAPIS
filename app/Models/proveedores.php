<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedores extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'Proveedores';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_proveedor';

    /**
     * No se utilizan timestamps automáticos.
     */
    public $timestamps = false;

    /**
     * Campos habilitados para asignación masiva.
     */
    protected $fillable = [
        'prov_nombre',
        'prov_contacto',
        'prov_telefono',
        'prov_email',
        'prov_direccion',
    ];
}
