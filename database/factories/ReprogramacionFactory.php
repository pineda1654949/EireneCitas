<?php

namespace Database\Factories;

use App\Models\Cita;
use App\Models\Reprogramacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reprogramacion>
 */
class ReprogramacionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cita_id' => Cita::factory(),
            'realizado_por' => null,
            'tipo' => Reprogramacion::TIPO_REPROGRAMACION,
            'fecha_anterior' => today()->addDays(3)->toDateString(),
            'hora_anterior' => '10:00',
            'fecha_nueva' => today()->addDays(5)->toDateString(),
            'hora_nueva' => '11:00',
            'motivo' => fake()->sentence(),
        ];
    }
}
