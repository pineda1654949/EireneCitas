<?php

namespace Database\Factories;

use App\Models\Promocion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promocion>
 */
class PromocionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => 'Paquete '.fake()->unique()->numberBetween(1, 9999),
            'descripcion' => fake()->sentence(),
            'numero_sesiones' => fake()->numberBetween(1, 8),
            'precio' => fake()->randomFloat(2, 50, 600),
            'activa' => true,
        ];
    }

    public function inactiva(): static
    {
        return $this->state(['activa' => false]);
    }
}
