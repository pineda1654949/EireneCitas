<?php

namespace App\Console\Commands;

use App\Enums\Rol;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Crea la cuenta de administrador en un servidor de produccion, donde no
 * se cargan los usuarios de prueba.
 */
class CrearAdministrador extends Command
{
    protected $signature = 'eirene:crear-admin
                            {--usuario= : Usuario o correo con el que iniciara sesion}
                            {--nombre=Administrador : Nombre visible}
                            {--contrasena= : Contrasena (si se omite, se pregunta de forma oculta)}';

    protected $description = 'Crea una cuenta de administrador';

    public function handle(): int
    {
        $usuario = $this->option('usuario') ?: $this->ask('Usuario o correo del administrador');
        $contrasena = $this->option('contrasena') ?: $this->secret('Contraseña (mínimo 8 caracteres, letras y números)');

        $validador = Validator::make(
            ['usuario' => $usuario, 'password' => $contrasena],
            [
                'usuario' => ['required', 'string', 'max:255', 'unique:users,email'],
                'password' => ['required', Password::defaults()],
            ],
        );

        if ($validador->fails()) {
            foreach ($validador->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $this->option('nombre'),
            'email' => $usuario,
            'password' => $contrasena,
            'role' => Rol::Administrador,
            'activo' => true,
        ]);

        $this->info("Administrador \"{$usuario}\" creado correctamente.");

        return self::SUCCESS;
    }
}
