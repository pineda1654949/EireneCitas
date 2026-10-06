<?php

namespace App\Notifications;

class CitaRegistrada extends NotificacionDeCita
{
    protected function asunto(): string
    {
        return 'Cita registrada';
    }

    protected function introduccion(): string
    {
        return 'Se registró una nueva cita. Quedará confirmada cuando se valide el pago.';
    }
}
