<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cotizaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CotizacionesController extends Controller
{
    public function index()
    {
        try {
            if (!Schema::hasTable('cotizaciones')) {
                return response()->json([], 200);
            }

            $cotizaciones = Cotizaciones::with($this->quoteRelations())
                ->orderByDesc('id_cotizacion')
                ->get();

            $cotizaciones->each(function ($cotizacion) {
                $fecha = $cotizacion->cot_fecha ? \Carbon\Carbon::parse($cotizacion->cot_fecha) : now();
                $vence = $fecha->copy()->addDays((int) ($cotizacion->cot_vigencia_dias ?? 0));
                $cotizacion->cot_estado = now()->lte($vence) ? 'Vigente' : 'Vencida';
            });

            return response()->json($cotizaciones, 200);
        } catch (Throwable $e) {
            return response()->json([], 200);
        }
    }

    public function store(Request $request)
    {
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'No se pudo guardar la cotizacion. Revisa cliente, empleado y conceptos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $supportsServices = Schema::hasColumn('detalle_cotizaciones', 'id_servicio');
                $validationError = $this->validateDetailsForSchema($request, $supportsServices);
                if ($validationError) {
                    return $validationError;
                }

                $cotizacion = Cotizaciones::create([
                    'id_cliente' => $request->id_cliente,
                    'id_empleado' => $request->id_empleado,
                    'cot_fecha' => now(),
                    'cot_vigencia_dias' => $request->cot_vigencia_dias ?? 15,
                    'cot_estado' => 'Vigente',
                    'cot_total' => $request->cot_total,
                ]);

                foreach ($request->input('detalles', []) as $item) {
                    $cotizacion->detalles()->create($this->detailPayload($item, $supportsServices));
                }

                return response()->json([
                    'message' => 'Guardado con exito',
                    'data' => $cotizacion->load($this->quoteRelations()),
                ], 201);
            });
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'No se pudo guardar la cotizacion por una restriccion de la base de datos.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = $this->validator($request, true);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'No se pudo actualizar la cotizacion. Revisa cliente, empleado y conceptos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request, $id) {
                $cotizacion = Cotizaciones::findOrFail($id);
                $supportsServices = Schema::hasColumn('detalle_cotizaciones', 'id_servicio');
                $validationError = $this->validateDetailsForSchema($request, $supportsServices);
                if ($validationError) {
                    return $validationError;
                }

                $cotizacion->update([
                    'id_cliente' => $request->id_cliente,
                    'id_empleado' => $request->id_empleado ?? $cotizacion->id_empleado,
                    'cot_vigencia_dias' => $request->cot_vigencia_dias,
                    'cot_estado' => 'Vigente',
                    'cot_total' => $request->cot_total,
                ]);

                $cotizacion->detalles()->delete();

                foreach ($request->input('detalles', []) as $item) {
                    $cotizacion->detalles()->create($this->detailPayload($item, $supportsServices));
                }

                return response()->json([
                    'message' => 'Actualizado correctamente',
                    'data' => $cotizacion->load($this->quoteRelations()),
                ], 200);
            });
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'No se pudo actualizar la cotizacion por una restriccion de la base de datos.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function show($id)
    {
        try {
            $cotizacion = Cotizaciones::with($this->quoteRelations())->findOrFail($id);
            return response()->json($cotizacion, 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'No se pudo cargar la cotizacion solicitada.',
            ], 404);
        }
    }

    public function destroy($id)
    {
        Cotizaciones::destroy($id);
        return response()->json(['message' => 'Cotizacion eliminada'], 200);
    }

    private function quoteRelations(): array
    {
        $relations = ['cliente', 'empleado', 'detalles.producto'];

        if (
            Schema::hasTable('detalle_cotizaciones') &&
            Schema::hasColumn('detalle_cotizaciones', 'id_servicio') &&
            Schema::hasTable('servicios')
        ) {
            $relations[] = 'detalles.servicio';
        }

        return $relations;
    }

    private function validator(Request $request, bool $updating = false)
    {
        $rules = [
            'id_cliente' => 'required|integer|exists:clientes,id_cliente',
            'id_empleado' => ($updating ? 'nullable' : 'required') . '|integer|exists:empleados,id_empleados',
            'cot_vigencia_dias' => 'required|integer|min:1',
            'cot_total' => 'required|numeric|min:0',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'nullable|integer|exists:productos,id_producto',
            'detalles.*.det_cantidad' => 'required|integer|min:1',
            'detalles.*.det_precio_unitario' => 'required|numeric|min:0',
        ];

        if (Schema::hasTable('servicios')) {
            $rules['detalles.*.id_servicio'] = 'nullable|integer|exists:servicios,id_servicio';
        } else {
            $rules['detalles.*.id_servicio'] = 'nullable|integer';
        }

        return Validator::make($request->all(), $rules);
    }

    private function validateDetailsForSchema(Request $request, bool $supportsServices)
    {
        foreach ($request->input('detalles', []) as $item) {
            if ((int) ($item['det_cantidad'] ?? 0) <= 0) {
                return response()->json(['message' => 'La cotizacion requiere piezas mayores a 0'], 422);
            }

            if (!$supportsServices && empty($item['id_producto'])) {
                return response()->json([
                    'message' => 'Esta base de datos aun no acepta servicios en cotizaciones. Selecciona un producto registrado.',
                ], 422);
            }

            if (empty($item['id_producto']) && empty($item['id_servicio'])) {
                return response()->json(['message' => 'Cada detalle requiere un producto o servicio registrado'], 422);
            }
        }

        return null;
    }

    private function detailPayload(array $item, bool $supportsServices): array
    {
        $payload = [
            'id_producto' => $item['id_producto'] ?? null,
            'det_cantidad' => $item['det_cantidad'],
            'det_precio_unitario' => $item['det_precio_unitario'],
        ];

        if ($supportsServices) {
            $payload['id_servicio'] = $item['id_servicio'] ?? null;
        }

        return $payload;
    }
}
