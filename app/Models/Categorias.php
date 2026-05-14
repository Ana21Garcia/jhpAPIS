<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorias extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'Categorias';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_categoria';

    /**
     * Indica si el modelo debe tener timestamps de Eloquent.
     */
    public $timestamps = false;

    /**
     * Campos habilitados para asignación masiva.
     */
    protected $fillable = [
        'cat_nombre',
        'cat_descripcion',
    ];
}
