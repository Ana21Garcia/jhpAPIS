<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => env('JHP_ADMIN_EMAIL', 'anne2jhp@gmail.com')],
            [
                'name' => env('JHP_ADMIN_NAME', 'Anne JHP'),
                'password' => Hash::make(env('JHP_ADMIN_PASSWORD', 'Sailor21$')),
                'email_verified_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
