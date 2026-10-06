<?php

namespace Database\Factories;

use App\Models\Horario;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Horario>
 */
class HorarioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'psicologo_id' => User::factory()->psicologo(),
            'dia_semana' => 1,
            'hora_inicio' => '09:00',
            'hora_fin' => '13:00',
            'activo' => true,
        ];
    }

    public function inactivo(): static
    {
        return $this->state(['activo' => false]);
    }
}
