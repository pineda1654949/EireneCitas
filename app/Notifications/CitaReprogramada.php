<?php

namespace App\Notifications;

class CitaReprogramada extends NotificacionDeCita
{
    protected function asunto(): string
    {
        return 'Cita reprogramada';
    }

    protected function introduccion(): string
    {
        return 'La cita fue reprogramada. Estos son los nuevos datos:';
    }
}
