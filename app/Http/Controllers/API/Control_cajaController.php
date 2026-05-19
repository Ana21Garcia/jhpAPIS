<?php

namespace App\Http\Controllers\API;

use App\Models\Control_caja;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Exception;

class Control_cajaController extends Controller
{
    /**
     * Verifica si hay una caja abierta actualmente.
     */
 public function consultarEstado()
{
    try {
        // Buscar caja abierta
        $caja = Control_caja::where('estado', 'Abierta')->first();

        if ($caja) {
            // Sumar ventas del día para esta caja
            $ventasHoy = DB::table('ventas')
                ->where('id_caja', $caja->id_caja)
                ->sum('ven_total');
            
            // Formatear el resultado
            $ventasHoy = $ventasHoy ? number_format((float)$ventasHoy, 2, '.', '') : '0.00';

            return response()->json([
                'status' => 'success',
                'caja_abierta' => true,
                'monto_inicial' => $caja->monto_inicial,
                'ventas_hoy' => $ventasHoy,
                'id_caja' => $caja->id_caja,
                'fecha_apertura' => $caja->fecha_apertura,
                'data' => $caja
            ], 200);
        }

        // Si no hay caja abierta, buscar la última caja cerrada para mostrar histórico
        $ultimaCaja = Control_caja::where('estado', 'Cerrada')
            ->latest('fecha_cierre')
            ->first();

        return response()->json([
            'status' => 'success',
            'caja_abierta' => false,
            'ventas_hoy' => '0.00',
            'ultima_caja' => $ultimaCaja ? [
                'id_caja' => $ultimaCaja->id_caja,
                'fecha_cierre' => $ultimaCaja->fecha_cierre,
                'total_ventas' => $ultimaCaja->monto_final_esperado - $ultimaCaja->monto_inicial
            ] : null
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al consultar estado: ' . $e->getMessage()
        ], 500);
    }
}

    public function index()
    {
        try {
            $controles = Control_caja::with('empleado')->get();
            return response()->json($controles, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

public function store(Request $request)
{
    $accion = $request->input('accion');

    try {
        // LÓGICA PARA ABRIR CAJA
        if ($accion === 'abrir') {
            // Verificar si ya hay una caja abierta
            $cajaAbiertaExistente = Control_caja::where('estado', 'Abierta')->first();
            
            if ($cajaAbiertaExistente) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Ya existe una caja abierta',
                    'caja_actual' => $cajaAbiertaExistente
                ], 400);
            }

            // Validar monto inicial
            if (!$request->has('monto_inicial') || $request->monto_inicial < 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El monto inicial es requerido y debe ser válido'
                ], 400);
            }

            // Crear nueva caja
            $caja = Control_caja::create([
                'monto_inicial'  => $request->monto_inicial,
                'id_empleado'    => $request->id_empleado ?? 1,
                'fecha_apertura' => now(),
                'estado'         => 'Abierta'
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Caja abierta con éxito',
                'data'    => $caja,
                'ventas_hoy' => '0.00',
                'caja_abierta' => true
            ], 201);
        }

        // LÓGICA PARA CERRAR CAJA - CORREGIDA
        if ($accion === 'cerrar') {
            $cajaAbierta = Control_caja::where('estado', 'Abierta')->first();
            
            if (!$cajaAbierta) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'No hay caja abierta para cerrar'
                ], 404);
            }

            // Calcular ventas de esta caja - USANDO EL ID_CAJA CORRECTO
            $ventasHoy = DB::table('ventas')
                ->where('id_caja', $cajaAbierta->id_caja)
                ->sum('ven_total');
            
            // Asegurar que sea un número
            $ventasHoy = $ventasHoy ? (float)$ventasHoy : 0;
            
            // Calcular monto final esperado (monto_inicial + ventas)
            $montoFinalEsperado = (float)$cajaAbierta->monto_inicial + $ventasHoy;

            // Actualizar la caja
            $cajaAbierta->update([
                'monto_final_esperado' => $montoFinalEsperado,
                'monto_real_cierre' => $request->monto_real_cierre ?? $montoFinalEsperado,
                'fecha_cierre'      => now(),
                'estado'            => 'Cerrada'
            ]);

            // Devolver respuesta con los valores calculados
            return response()->json([
                'status'  => 'success',
                'message' => 'Caja cerrada correctamente',
                'data'    => $cajaAbierta,
                'ventas_hoy' => number_format($ventasHoy, 2, '.', ''), // ← AHORA SÍ ENVIAMOS LAS VENTAS
                'monto_final_esperado' => number_format($montoFinalEsperado, 2, '.', '')
            ], 200);
        }

        return response()->json([
            'status' => 'error', 
            'message' => 'Acción no válida. Use "abrir" o "cerrar"'
        ], 400);

    } catch (Exception $e) {
        return response()->json([
            'status' => 'error', 
            'message' => 'Error en el servidor: ' . $e->getMessage()
        ], 500);
    }
}

    public function show($id)
    {
        try {
            $caja = Control_caja::with('empleado')->findOrFail($id);
            return response()->json($caja, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $caja = Control_caja::find($id);
            if (!$caja) {
                return response()->json(['error' => 'Registro no encontrado'], 404);
            }
            
            $caja->delete();
            return response()->json(['message' => 'Registro eliminado'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
