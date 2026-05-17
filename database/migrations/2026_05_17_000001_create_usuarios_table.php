<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('usuarios')) {
            Schema::create('usuarios', function (Blueprint $table) {
                $table->id('id_usuario');
                $table->string('correo', 100)->unique();
                $table->string('password', 255);
                $table->enum('tipo_usuario', ['Empleado', 'Admin', 'Cliente'])->default('Cliente');
                $table->unsignedBigInteger('id_empleado')->nullable();
                $table->unsignedBigInteger('id_cliente')->nullable();
                $table->enum('estado', ['Activo', 'Inactivo', 'Bloqueado'])->default('Activo');
                $table->timestamp('email_verified_at')->nullable();
                $table->timestamp('ultimo_acceso')->nullable();
                $table->timestamps();
                
                // Índices para búsquedas rápidas
                $table->index('correo');
                $table->index('tipo_usuario');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
