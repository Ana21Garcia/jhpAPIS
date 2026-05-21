<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $email = env('JHP_ADMIN_EMAIL', 'anne2jhp@gmail.com');
        $now = now();

        $user = User::firstOrNew(['email' => $email]);
        $user->name = env('JHP_ADMIN_NAME', 'Anne JHP');
        $user->password = Hash::make(env('JHP_ADMIN_PASSWORD', 'Sailor21$'));
        $user->email_verified_at = $now;
        $user->created_at = $user->created_at ?: $now;
        $user->updated_at = $now;
        $user->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
