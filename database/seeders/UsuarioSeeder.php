<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Empleado;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario admin
        $admin = Usuario::create([
            'correo' => 'admin@jhpapi.com',
            'password' => Hash::make('Admin@123'),
            'tipo_usuario' => 'Admin',
            'estado' => 'Activo',
            'email_verified_at' => now(),
        ]);

        // Crear usuario empleado
        $empleado = Usuario::create([
            'correo' => 'empleado@jhpapi.com',
            'password' => Hash::make('Empleado@123'),
            'tipo_usuario' => 'Empleado',
            'estado' => 'Activo',
            'email_verified_at' => now(),
        ]);

        // Crear usuario cliente
        $cliente = Usuario::create([
            'correo' => 'cliente@jhpapi.com',
            'password' => Hash::make('Cliente@123'),
            'tipo_usuario' => 'Cliente',
            'estado' => 'Activo',
            'email_verified_at' => now(),
        ]);

        // Crear usuario para pruebas de recuperación
        $prueba = Usuario::create([
            'correo' => 'prueba@jhpapi.com',
            'password' => Hash::make('Prueba@123'),
            'tipo_usuario' => 'Cliente',
            'estado' => 'Activo',
            'email_verified_at' => now(),
        ]);

        echo "Usuarios creados exitosamente:\n";
        echo "- Admin: admin@jhpapi.com / Admin@123\n";
        echo "- Empleado: empleado@jhpapi.com / Empleado@123\n";
        echo "- Cliente: cliente@jhpapi.com / Cliente@123\n";
        echo "- Prueba: prueba@jhpapi.com / Prueba@123\n";
    }
}
