<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Correo de recuperacion de contrasena en espanol.
 */
class RestablecerContrasena extends ResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        $minutos = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Restablecer contraseña - '.config('eirene.clinica.nombre'))
            ->greeting('Hola:')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
            ->action('Restablecer contraseña', $url)
            ->line("Este enlace vence en {$minutos} minutos.")
            ->line('Si no solicitaste el cambio, puedes ignorar este correo; tu contraseña no se modificará.')
            ->salutation('Atentamente, '.config('eirene.clinica.nombre'));
    }
}
