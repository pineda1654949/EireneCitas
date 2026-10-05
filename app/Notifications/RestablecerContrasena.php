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
            ->subject('Restablecer contrasena - '.config('eirene.clinica.nombre'))
            ->greeting('Hola:')
            ->line('Recibimos una solicitud para restablecer la contrasena de tu cuenta.')
            ->action('Restablecer contrasena', $url)
            ->line("Este enlace vence en {$minutos} minutos.")
            ->line('Si no solicitaste el cambio, puedes ignorar este correo; tu contrasena no se modificara.')
            ->salutation('Atentamente, '.config('eirene.clinica.nombre'));
    }
}
