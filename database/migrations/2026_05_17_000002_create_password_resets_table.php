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
        if (!Schema::hasTable('password_resets')) {
            Schema::create('password_resets', function (Blueprint $table) {
                $table->id('id_reset');
                $table->unsignedBigInteger('id_usuario')->nullable();
                $table->string('token', 255)->unique();
                $table->string('correo', 100);
                $table->timestamp('fecha_solicitud')->useCurrent();
            $table->dateTime('fecha_expiracion');
                $table->timestamp('fecha_uso')->nullable();
                $table->string('ip_solicitud', 45)->nullable();
                $table->text('user_agent')->nullable();
                
                // Índices
                $table->index('id_usuario');
                $table->index('token');
                $table->index('correo');
                
                // Foreign key
                $table->foreign('id_usuario')
                    ->references('id_usuario')
                    ->on('usuarios')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_resets');
    }
};
