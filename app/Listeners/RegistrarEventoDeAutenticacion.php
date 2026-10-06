<?php

namespace App\Listeners;

use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

/**
 * Deja constancia en la auditoria de los inicios y cierres de sesion,
 * los intentos fallidos y los restablecimientos de contrasena.
 */
class RegistrarEventoDeAutenticacion
{
    public function handle(Login|Logout|Failed|PasswordReset $evento): void
    {
        $usuario = $evento->user instanceof User ? $evento->user : null;

        [$nombre, $datos] = match (true) {
            $evento instanceof Login => ['inicio_sesion', null],
            $evento instanceof Logout => ['cierre_sesion', null],
            $evento instanceof PasswordReset => ['contrasena_restablecida', null],
            default => ['inicio_sesion_fallido', ['usuario' => $evento->credentials['email'] ?? null]],
        };

        Auditoria::registrar($nombre, $usuario, null, $datos, $usuario?->id);
    }
}
