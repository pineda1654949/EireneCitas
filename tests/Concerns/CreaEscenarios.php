<?php

namespace Tests\Concerns;

use App\Enums\EstadoCita;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Ayudantes para construir los escenarios de prueba mas comunes.
 */
trait CreaEscenarios
{
    /** Proximo lunes respecto de la fecha fija de las pruebas (2026-10-12). */
    protected function proximoLunes(): string
    {
        return Carbon::now()->next(Carbon::MONDAY)->toDateString();
    }

    /**
     * Psicologo activo que atiende una especialidad, con bloque de lunes
     * 09:00-13:00 (horas ofrecidas: 09:00, 10:00, 11:00 y 12:00).
     *
     * @return array{0: User, 1: Especialidad}
     */
    protected function psicologoConAgenda(int $diaSemana = Carbon::MONDAY, string $inicio = '09:00', string $fin = '13:00'): array
    {
        $psicologo = User::factory()->psicologo()->create();
        $especialidad = Especialidad::factory()->create();
        $psicologo->especialidades()->attach($especialidad);

        Horario::factory()->create([
            'psicologo_id' => $psicologo->id,
            'dia_semana' => $diaSemana,
            'hora_inicio' => $inicio,
            'hora_fin' => $fin,
        ]);

        return [$psicologo, $especialidad];
    }

    protected function usuarioPaciente(): User
    {
        return User::factory()->paciente()->create();
    }

    protected function fichaDe(User $usuario): Paciente
    {
        return Paciente::where('user_id', $usuario->id)->firstOrFail();
    }

    /**
     * Cita en la agenda del psicologo indicado.
     *
     * @param  array<string, mixed>  $atributos
     */
    protected function citaPara(User $psicologo, ?Paciente $paciente = null, array $atributos = []): Cita
    {
        return Cita::factory()->create([
            'psicologo_id' => $psicologo->id,
            'paciente_id' => $paciente?->id ?? Paciente::factory(),
            'especialidad_id' => $psicologo->especialidades()->first()?->id ?? Especialidad::factory(),
            'fecha' => $this->proximoLunes(),
            'hora' => '10:00',
            'estado' => EstadoCita::Pendiente,
            ...$atributos,
        ]);
    }
}
