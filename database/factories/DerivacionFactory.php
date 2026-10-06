<?php

namespace Database\Factories;

use App\Enums\EstadoDerivacion;
use App\Models\Derivacion;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Derivacion>
 */
class DerivacionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'paciente_id' => Paciente::factory(),
            'psicologo_id' => User::factory()->psicologo(),
            'especialidad_id' => null,
            'derivado_por' => User::factory()->recepcionista(),
            'observaciones' => fake()->sentence(),
            'estado' => EstadoDerivacion::Pendiente,
        ];
    }

    public function aceptada(): static
    {
        return $this->state(['estado' => EstadoDerivacion::Aceptada, 'respondida_at' => now()]);
    }

    public function rechazada(): static
    {
        return $this->state([
            'estado' => EstadoDerivacion::Rechazada,
            'motivo_rechazo' => 'No atiendo casos de esta especialidad.',
            'respondida_at' => now(),
        ]);
    }
}
