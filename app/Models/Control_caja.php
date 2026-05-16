<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Control_Caja extends Model
{
    
    protected $table = 'Control_Caja';

   
    protected $primaryKey = 'id_caja';

   
    public $timestamps = false;

   
    protected $fillable = [
        'id_empleado',
        'fecha_apertura',
        'monto_inicial',
        'fecha_cierre',
        'monto_final_esperado',
        'monto_real_cierre',
        'estado',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleados::class, 'id_empleado', 'id_empleados');
    }
}
