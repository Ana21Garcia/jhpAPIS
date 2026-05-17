<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('password_resets')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE password_resets DROP FOREIGN KEY password_resets_id_usuario_foreign');
        } catch (\Exception $e) {
            // Ignore if the foreign key does not exist.
        }

        DB::statement('ALTER TABLE password_resets MODIFY id_usuario BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('password_resets')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE password_resets MODIFY id_usuario BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE password_resets ADD CONSTRAINT password_resets_id_usuario_foreign FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE');
        } catch (\Exception $e) {
            // Ignore if the migration cannot be rolled back cleanly.
        }
    }
};
