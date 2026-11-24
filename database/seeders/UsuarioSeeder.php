<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::create([
            'nombreUsuario' => 'admin',
            'passwordHash' => Hash::make('admin123'), 
            'rol' => 'Administrador',
            'estadoCuenta' => 'Activa',
            'intentosFallidos' => 0,
        ]);

        Usuario::create([
            'nombreUsuario' => 'recepcion',
            'passwordHash' => Hash::make('recep123'), 
            'rol' => 'Recepcionista',
            'estadoCuenta' => 'Activa',
            'intentosFallidos' => 0,
        ]);
    }
}