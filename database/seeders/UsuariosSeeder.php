<?php

namespace Database\Seeders;

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    /**
     * Crea un usuario de cada rol para poder probar el sistema de inmediato.
     * IMPORTANTE: cambiar estas contrasenas antes de pasar a produccion.
     */
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@eirene.test'],
            [
                'name' => 'Administrador',
                'apellidos' => 'Eirene',
                'password' => Hash::make('password'),
                'role' => 'administrador',
                'activo' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'recepcion@eirene.test'],
            [
                'name' => 'Encargada',
                'apellidos' => 'Administrativa',
                'password' => Hash::make('password'),
                'role' => 'recepcionista',
                'activo' => true,
            ]
        );

        $psico1 = User::updateOrCreate(
            ['email' => 'psicologo1@eirene.test'],
            [
                'name' => 'Maria',
                'apellidos' => 'Fernandez',
                'password' => Hash::make('password'),
                'role' => 'psicologo',
                'activo' => true,
            ]
        );

        $psico2 = User::updateOrCreate(
            ['email' => 'psicologo2@eirene.test'],
            [
                'name' => 'Carlos',
                'apellidos' => 'Ramirez',
                'password' => Hash::make('password'),
                'role' => 'psicologo',
                'activo' => true,
            ]
        );

        $pacienteUser = User::updateOrCreate(
            ['email' => 'paciente@eirene.test'],
            [
                'name' => 'Ana',
                'apellidos' => 'Torres',
                'password' => Hash::make('password'),
                'role' => 'paciente',
                'activo' => true,
            ]
        );

        Paciente::updateOrCreate(
            ['user_id' => $pacienteUser->id],
            [
                'nombres' => 'Ana',
                'apellidos' => 'Torres',
                'dni' => '70000001',
                'edad' => 24,
                'correo' => 'paciente@eirene.test',
                'telefono' => '987654321',
            ]
        );
    }
}
