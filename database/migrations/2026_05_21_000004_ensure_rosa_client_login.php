<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clientes')) {
            return;
        }

        $values = [
            'cli_nombre' => env('JHP_CLIENT_NAME', 'Rosa'),
            'cli_apaterno' => env('JHP_CLIENT_APATERNO', 'Cliente'),
            'cli_amaterno' => env('JHP_CLIENT_AMATERNO', ''),
            'cli_telefono' => env('JHP_CLIENT_PHONE', '0000000000'),
        ];

        if (Schema::hasColumn('clientes', 'cli_password')) {
            $values['cli_password'] = Hash::make(env('JHP_CLIENT_PASSWORD', 'Sailor24$'));
        }

        if (Schema::hasColumn('clientes', 'tipo_usuario')) {
            $values['tipo_usuario'] = 3;
        }

        if (Schema::hasColumn('clientes', 'cli_estado')) {
            $values['cli_estado'] = 'Activo';
        }

        if (Schema::hasColumn('clientes', 'cli_fecha_registro')) {
            $values['cli_fecha_registro'] = now();
        }

        if (Schema::hasColumn('clientes', 'updated_at')) {
            $values['updated_at'] = now();
        }

        if (Schema::hasColumn('clientes', 'created_at')) {
            $values['created_at'] = now();
        }

        DB::table('clientes')->updateOrInsert(
            ['cli_correo' => env('JHP_CLIENT_EMAIL', 'rosa@gmail.com')],
            $values,
        );
    }

    public function down(): void
    {
        //
    }
};
