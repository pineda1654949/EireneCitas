<?php

namespace Database\Factories;

use App\Enums\EstadoPago;
use App\Enums\MetodoPago;
use App\Models\Cita;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pago>
 */
class PagoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cita_id' => Cita::factory(),
            'monto' => 80,
            'metodo_pago' => fake()->randomElement(MetodoPago::cases()),
            'estado' => EstadoPago::Pendiente,
            'numero_comprobante' => fake()->bothify('OP-#####'),
        ];
    }

    public function confirmado(): static
    {
        return $this->state(['estado' => EstadoPago::Confirmado, 'fecha_pago' => now()]);
    }

    public function rechazado(): static
    {
        return $this->state(['estado' => EstadoPago::Rechazado]);
    }
}
