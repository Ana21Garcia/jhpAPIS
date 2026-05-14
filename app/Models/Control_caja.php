<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Control_Caja extends Model
{
    /**
     * Tabla del modelo.
     */
    protected $table = 'Control_Caja';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_caja';

    /**
     * Se desactiva el manejo automático de timestamps (created_at/updated_at)
     * ya que usas nombres personalizados como fecha_apertura.
     */
    public $timestamps = false;

    /**
     * Campos habilitados para asignación masiva.
     */
    protected $fillable = [
        'id_empleado',
        'fecha_apertura',
        'monto_inicial',
        'fecha_cierre',
        'monto_final_esperado',
        'monto_real_cierre',
        'estado',
    ];

    /**
     * Relación con el modelo Empleado.
     */
    public function empleado()
    {
        return $this->belongsTo(Empleados::class, 'id_empleado', 'id_empleados');
    }
}
