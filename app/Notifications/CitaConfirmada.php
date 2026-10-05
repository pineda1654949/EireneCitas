<?php

namespace App\Notifications;

class CitaConfirmada extends NotificacionDeCita
{
    protected function asunto(): string
    {
        return 'Cita confirmada';
    }

    protected function introduccion(): string
    {
        return 'Tu cita fue confirmada. Te esperamos en la fecha y hora indicadas.';
    }
}
