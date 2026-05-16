<?php

namespace App\Http\Controllers\API;

use App\Models\Detalle_Cotizaciones;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DetalleCotizacionController extends Controller
{
    // 
    public function index()
    {
        $detalles = Detalle_Cotizaciones::with(['producto', 'servicio'])->get();
        return response()->json($detalles, 200);
    }

    // Agregar una línea  a la cotización
    public function store(Request $request)
    {
        $detalle = Detalle_Cotizaciones::create($request->all());

        return response()->json([
            'message' => 'Línea agregada a la cotización',
            'data' => $detalle
        ], 201);
    }

    // Mostrar una línea específica
    public function show($id)
    {
        $detalle = Detalle_Cotizaciones::with(['producto', 'servicio'])->findOrFail($id);
        return response()->json($detalle, 200);
    }

    // Actualizar cantidad o precio de la línea
    public function update(Request $request, $id)
    {
        $detalle = Detalle_Cotizaciones::findOrFail($id);
        $detalle->update($request->all());

        return response()->json([
            'message' => 'Detalle de cotización actualizado',
            'data' => $detalle
        ], 200);
    }

    // Eliminar la línea de la cotización
    public function destroy($id)
    {
        Detalle_Cotizaciones::destroy($id);

        return response()->json([
            'message' => 'Línea eliminada de la cotización'
        ], 200);
    }
}