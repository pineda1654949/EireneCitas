<?php

namespace Database\Factories;

use App\Enums\EstadoCita;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cita>
 */
class CitaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'paciente_id' => Paciente::factory(),
            'psicologo_id' => User::factory()->psicologo(),
            'especialidad_id' => Especialidad::factory(),
            'promocion_id' => null,
            'fecha' => today()->addWeek()->toDateString(),
            'hora' => '10:00',
            'motivo_consulta' => fake()->sentence(),
            'estado' => EstadoCita::Pendiente,
            'numero_reprogramaciones' => 0,
        ];
    }

    public function estado(EstadoCita $estado): static
    {
        return $this->state(['estado' => $estado]);
    }

    public function confirmada(): static
    {
        return $this->estado(EstadoCita::Confirmada);
    }

    public function cancelada(): static
    {
        return $this->estado(EstadoCita::Cancelada);
    }

    public function atendida(): static
    {
        return $this->estado(EstadoCita::Atendida);
    }
}
