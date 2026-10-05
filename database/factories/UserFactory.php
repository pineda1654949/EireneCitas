<?php

namespace Database\Factories;

use App\Enums\Rol;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /** Contrasena de todos los usuarios generados en pruebas. */
    public const CONTRASENA = 'Secreta123';

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'apellidos' => fake()->lastName().' '.fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= self::CONTRASENA,
            'dni' => fake()->unique()->numerify('########'),
            'telefono' => fake()->numerify('9########'),
            'role' => Rol::Paciente,
            'activo' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function administrador(): static
    {
        return $this->state(['role' => Rol::Administrador]);
    }

    public function recepcionista(): static
    {
        return $this->state(['role' => Rol::Recepcionista]);
    }

    public function psicologo(): static
    {
        return $this->state(['role' => Rol::Psicologo]);
    }

    /**
     * Paciente con su ficha clinica vinculada.
     */
    public function paciente(): static
    {
        return $this->state(['role' => Rol::Paciente])
            ->afterCreating(function (User $usuario) {
                Paciente::factory()->create([
                    'user_id' => $usuario->id,
                    'nombres' => $usuario->name,
                    'apellidos' => (string) $usuario->apellidos,
                    'correo' => $usuario->email,
                ]);
            });
    }

    public function inactivo(): static
    {
        return $this->state(['activo' => false]);
    }
}
