<?php

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Usuarios de prueba (uno por rol), sus especialidades y horarios.
 * SOLO para desarrollo y demostracion: DatabaseSeeder no lo ejecuta en
 * produccion. Contrasena de todos: "contraseña".
 */
class UsuariosDemoSeeder extends Seeder
{
    public const CONTRASENA = 'contraseña';

    public function run(): void
    {
        $this->usuario('admin', 'Administrador', 'Eirene', Rol::Administrador);
        $this->usuario('recepcion@eirene.test', 'Encargada', 'Administrativa', Rol::Recepcionista);

        $psicologa = $this->usuario('psicologo1@eirene.test', 'Maria', 'Fernandez', Rol::Psicologo);
        $psicologo = $this->usuario('psicologo2@eirene.test', 'Carlos', 'Ramirez', Rol::Psicologo);

        $psicologa->especialidades()->sync(
            Especialidad::whereIn('nombre', ['Ansiedad y estres', 'Depresion', 'Autoestima y desarrollo personal'])->pluck('id')
        );
        $psicologo->especialidades()->sync(
            Especialidad::whereIn('nombre', ['Terapia de pareja', 'Terapia infantil'])->pluck('id')
        );

        // Lunes a viernes, 09:00-13:00 y 15:00-18:00.
        foreach ([$psicologa, $psicologo] as $profesional) {
            foreach (range(1, 5) as $dia) {
                // Formato HH:MM:SS, igual al que guarda el modelo (DEF-016).
                foreach ([['09:00:00', '13:00:00'], ['15:00:00', '18:00:00']] as [$inicio, $fin]) {
                    Horario::firstOrCreate([
                        'psicologo_id' => $profesional->id,
                        'dia_semana' => $dia,
                        'hora_inicio' => $inicio,
                        'hora_fin' => $fin,
                    ]);
                }
            }
        }

        $paciente = $this->usuario('paciente@eirene.test', 'Ana', 'Torres', Rol::Paciente);

        Paciente::updateOrCreate(
            ['user_id' => $paciente->id],
            [
                'nombres' => 'Ana',
                'apellidos' => 'Torres',
                'dni' => '70000001',
                'edad' => 24,
                'correo' => 'paciente@eirene.test',
                'telefono' => '987654321',
            ],
        );
    }

    private function usuario(string $email, string $nombre, string $apellidos, Rol $rol): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $nombre,
                'apellidos' => $apellidos,
                'password' => self::CONTRASENA,
                'role' => $rol,
                'activo' => true,
            ],
        );
    }
}
