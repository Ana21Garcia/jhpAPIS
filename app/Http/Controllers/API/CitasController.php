<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Citas;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CitasController extends Controller
{
    public function index()
    {
        try {
            $this->ensureAppointmentsSchema();
            $citas = Citas::with(['cliente', 'empleado'])->get();
            return response()->json($citas, 200);
        } catch (Throwable $e) {
            return response()->json([], 200);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->ensureAppointmentsSchema();
            $validator = Validator::make($request->all(), $this->rules());

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'No se pudo guardar la cita. Revisa cliente, empleado, fecha y estado.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $foreignKeyError = $this->validateExistingRelations($request);
            if ($foreignKeyError) {
                return $foreignKeyError;
            }

            $cita = Citas::create($validator->validated());
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'No se pudo guardar la cita. Revisa que la tabla citas este actualizada.',
                'error' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Cita programada con exito',
            'data' => $cita,
        ], 201);
    }

    public function show($id)
    {
        try {
            $this->ensureAppointmentsSchema();
            $cita = Citas::with(['cliente', 'empleado'])->findOrFail($id);
            return response()->json($cita, 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'No se pudo cargar la cita solicitada.',
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->ensureAppointmentsSchema();
            $cita = Citas::findOrFail($id);

            $validator = Validator::make($request->all(), $this->rules(true));

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'No se pudo actualizar la cita. Revisa cliente, empleado, fecha y estado.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $foreignKeyError = $this->validateExistingRelations($request);
            if ($foreignKeyError) {
                return $foreignKeyError;
            }

            $cita->update($validator->validated());
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'No se pudo actualizar la cita por una restriccion de la base de datos.',
                'error' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Cita actualizada correctamente',
            'data' => $cita,
        ], 200);
    }

    public function destroy($id)
    {
        try {
            $this->ensureAppointmentsSchema();
            Citas::destroy($id);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'No se pudo eliminar la cita.',
                'error' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Cita eliminada del sistema',
        ], 200);
    }

    private function rules(bool $updating = false): array
    {
        return [
            'id_cliente' => ($updating ? 'sometimes|' : '') . 'nullable|integer',
            'id_empleado' => ($updating ? 'sometimes|' : '') . 'nullable|integer',
            'cita_fecha_programada' => ($updating ? 'sometimes|required' : 'required') . '|date',
            'cita_motivo' => ($updating ? 'sometimes|' : '') . 'nullable|string|max:255',
            'cita_estado' => ($updating ? 'sometimes|' : '') . 'nullable|in:Pendiente,Confirmada,Cancelada,Realizada',
            'cita_notas' => ($updating ? 'sometimes|' : '') . 'nullable|string',
        ];
    }

    private function validateExistingRelations(Request $request)
    {
        if (
            $request->filled('id_cliente') &&
            Schema::hasTable('clientes') &&
            !\App\Models\Cliente::where('id_cliente', $request->id_cliente)->exists()
        ) {
            return response()->json(['message' => 'El cliente seleccionado no existe en la base de datos.'], 422);
        }

        if (
            $request->filled('id_empleado') &&
            Schema::hasTable('empleados') &&
            !\App\Models\Empleado::where('id_empleados', $request->id_empleado)->exists()
        ) {
            return response()->json(['message' => 'El empleado seleccionado no existe en la base de datos.'], 422);
        }

        return null;
    }

    private function ensureAppointmentsSchema(): void
    {
        if (!Schema::hasTable('citas')) {
            Schema::create('citas', function (Blueprint $table) {
                $table->increments('id_cita');
                $table->unsignedInteger('id_cliente')->nullable();
                $table->unsignedInteger('id_empleado')->nullable();
                $table->dateTime('cita_fecha_programada');
                $table->string('cita_motivo', 255)->nullable();
                $table->string('cita_estado', 30)->default('Pendiente');
                $table->text('cita_notas')->nullable();
                $table->timestamps();
                $table->index(['id_cliente', 'cita_estado']);
                $table->index('cita_fecha_programada');
            });
            return;
        }

        Schema::table('citas', function (Blueprint $table) {
            if (!Schema::hasColumn('citas', 'id_cliente')) {
                $table->unsignedInteger('id_cliente')->nullable();
            }
            if (!Schema::hasColumn('citas', 'id_empleado')) {
                $table->unsignedInteger('id_empleado')->nullable();
            }
            if (!Schema::hasColumn('citas', 'cita_fecha_programada')) {
                $table->dateTime('cita_fecha_programada')->nullable();
            }
            if (!Schema::hasColumn('citas', 'cita_motivo')) {
                $table->string('cita_motivo', 255)->nullable();
            }
            if (!Schema::hasColumn('citas', 'cita_estado')) {
                $table->string('cita_estado', 30)->default('Pendiente');
            }
            if (!Schema::hasColumn('citas', 'cita_notas')) {
                $table->text('cita_notas')->nullable();
            }
            if (!Schema::hasColumn('citas', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('citas', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }
}
