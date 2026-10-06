<?php

namespace Database\Factories;

use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paciente>
 */
class PacienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null,
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName().' '.fake()->lastName(),
            'dni' => fake()->unique()->numerify('########'),
            'edad' => fake()->numberBetween(6, 80),
            'correo' => fake()->unique()->safeEmail(),
            'telefono' => fake()->numerify('9########'),
            'direccion' => fake()->streetAddress(),
            'motivo_consulta' => fake()->sentence(),
        ];
    }

    public function sinCorreo(): static
    {
        return $this->state(['correo' => null]);
    }
}
