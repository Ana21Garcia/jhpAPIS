<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CitasController extends Controller
{
    public function index()
    {
        try {
            $this->ensureAppointmentsSchema();
            $citas = DB::table('citas')
                ->leftJoin('clientes', 'citas.id_cliente', '=', 'clientes.id_cliente')
                ->leftJoin('empleados', 'citas.id_empleado', '=', 'empleados.id_empleados')
                ->select('citas.*')
                ->selectRaw('clientes.cli_nombre, clientes.cli_apaterno, clientes.cli_amaterno')
                ->selectRaw('empleados.emp_nombre, empleados.emp_apaterno, empleados.emp_amaterno')
                ->orderByDesc('citas.id_cita')
                ->get()
                ->map(fn ($cita) => $this->formatAppointment($cita));
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

            $payload = $this->appointmentPayload($validator->validated());
            $id = DB::table('citas')->insertGetId($payload);
            $cita = DB::table('citas')->where('id_cita', $id)->first();
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
            $cita = DB::table('citas')->where('id_cita', $id)->first();
            if (!$cita) {
                return response()->json(['message' => 'No se encontro la cita solicitada.'], 404);
            }
            return response()->json($this->formatAppointment($cita), 200);
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
            $cita = DB::table('citas')->where('id_cita', $id)->first();
            if (!$cita) {
                return response()->json(['message' => 'No se encontro la cita solicitada.'], 404);
            }

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

            DB::table('citas')
                ->where('id_cita', $id)
                ->update($this->appointmentPayload($validator->validated(), true));
            $cita = DB::table('citas')->where('id_cita', $id)->first();
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
            DB::table('citas')->where('id_cita', $id)->delete();
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
            !DB::table('clientes')->where('id_cliente', $request->id_cliente)->exists()
        ) {
            return response()->json(['message' => 'El cliente seleccionado no existe en la base de datos.'], 422);
        }

        if (
            $request->filled('id_empleado') &&
            Schema::hasTable('empleados') &&
            !DB::table('empleados')->where('id_empleados', $request->id_empleado)->exists()
        ) {
            return response()->json(['message' => 'El empleado seleccionado no existe en la base de datos.'], 422);
        }

        return null;
    }

    private function appointmentPayload(array $data, bool $updating = false): array
    {
        $payload = array_intersect_key($data, array_flip([
            'id_cliente',
            'id_empleado',
            'cita_fecha_programada',
            'cita_motivo',
            'cita_estado',
            'cita_notas',
        ]));

        if (array_key_exists('id_cliente', $payload) && $payload['id_cliente'] === null) {
            unset($payload['id_cliente']);
        }

        if (array_key_exists('id_empleado', $payload) && $payload['id_empleado'] === null) {
            unset($payload['id_empleado']);
        }

        $now = now();
        if (!$updating && Schema::hasColumn('citas', 'created_at')) {
            $payload['created_at'] = $now;
        }
        if (Schema::hasColumn('citas', 'updated_at')) {
            $payload['updated_at'] = $now;
        }

        return $payload;
    }

    private function formatAppointment(object $cita): array
    {
        $cliente = trim(implode(' ', array_filter([
            $cita->cli_nombre ?? null,
            $cita->cli_apaterno ?? null,
            $cita->cli_amaterno ?? null,
        ])));
        $empleado = trim(implode(' ', array_filter([
            $cita->emp_nombre ?? null,
            $cita->emp_apaterno ?? null,
            $cita->emp_amaterno ?? null,
        ])));

        return [
            'id_cita' => $cita->id_cita ?? null,
            'id_cliente' => $cita->id_cliente ?? null,
            'id_empleado' => $cita->id_empleado ?? null,
            'cita_fecha_programada' => $cita->cita_fecha_programada ?? null,
            'cita_motivo' => $cita->cita_motivo ?? null,
            'cita_estado' => $cita->cita_estado ?? 'Pendiente',
            'cita_notas' => $cita->cita_notas ?? null,
            'created_at' => $cita->created_at ?? null,
            'updated_at' => $cita->updated_at ?? null,
            'cliente' => $cliente ? [
                'cli_nombre' => $cita->cli_nombre ?? null,
                'cli_apaterno' => $cita->cli_apaterno ?? null,
                'cli_amaterno' => $cita->cli_amaterno ?? null,
            ] : null,
            'empleado' => $empleado ? [
                'emp_nombre' => $cita->emp_nombre ?? null,
                'emp_apaterno' => $cita->emp_apaterno ?? null,
                'emp_amaterno' => $cita->emp_amaterno ?? null,
            ] : null,
        ];
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
