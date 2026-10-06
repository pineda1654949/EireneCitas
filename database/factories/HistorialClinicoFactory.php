<?php

namespace Database\Factories;

use App\Models\Cita;
use App\Models\HistorialClinico;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HistorialClinico>
 */
class HistorialClinicoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cita_id' => Cita::factory()->atendida(),
            'paciente_id' => fn (array $atributos) => Cita::find($atributos['cita_id'])?->paciente_id,
            'psicologo_id' => fn (array $atributos) => Cita::find($atributos['cita_id'])?->psicologo_id,
            'notas_sesion' => fake()->paragraph(),
            'avance' => fake()->randomElement(['Inicial', 'En progreso', 'Alta']),
        ];
    }
}
