<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorias extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'categorias';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_categoria';

   
    public $timestamps = false;

    
    protected $fillable = [
        'cat_nombre',
        'cat_descripcion',
    ];
}
