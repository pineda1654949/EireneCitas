<?php

namespace Database\Seeders;

use App\Enums\Rol;
use App\Exceptions\EntornoNoPermitidoException;
use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Datos para las pruebas de carga con k6 (Fase 8, CP-SYS-24 a CP-SYS-28).
 * Uso: php artisan db:seed --class=CargaSeeder (solo en la base eirene_k6).
 * Contrasena de todas las cuentas: Carga2026x
 */
class CargaSeeder extends Seeder
{
    public const CONTRASENA = 'Carga2026x';

    public const PACIENTES = 120;

    public function run(): void
    {
        if (app()->isProduction()) {
            throw EntornoNoPermitidoException::enProduccion('CargaSeeder');
        }

        $especialidad = Especialidad::firstOrCreate(['nombre' => 'Ansiedad y estres']);

        // Psicologos con agenda de lunes a sabado, 08:00 a 20:00.
        foreach (range(1, 3) as $n) {
            $psicologo = User::updateOrCreate(
                ['email' => "k6_psicologo_{$n}@eirene.test"],
                ['name' => "Psicologo{$n}", 'apellidos' => 'Carga', 'password' => self::CONTRASENA, 'role' => Rol::Psicologo, 'activo' => true],
            );
            $psicologo->especialidades()->syncWithoutDetaching([$especialidad->id]);

            foreach (range(1, 6) as $dia) {
                Horario::firstOrCreate([
                    'psicologo_id' => $psicologo->id, 'dia_semana' => $dia,
                    'hora_inicio' => '08:00:00', 'hora_fin' => '20:00:00',
                ]);
            }
        }

        User::updateOrCreate(
            ['email' => 'k6_recepcion@eirene.test'],
            ['name' => 'Recepcion', 'apellidos' => 'Carga', 'password' => self::CONTRASENA, 'role' => Rol::Recepcionista, 'activo' => true],
        );

        foreach (range(1, self::PACIENTES) as $n) {
            $usuario = User::updateOrCreate(
                ['email' => "k6_paciente_{$n}@eirene.test"],
                ['name' => "Paciente{$n}", 'apellidos' => 'Carga', 'password' => self::CONTRASENA, 'role' => Rol::Paciente, 'activo' => true],
            );

            Paciente::updateOrCreate(
                ['user_id' => $usuario->id],
                ['nombres' => "Paciente{$n}", 'apellidos' => 'Carga', 'correo' => $usuario->email],
            );
        }
    }
}
