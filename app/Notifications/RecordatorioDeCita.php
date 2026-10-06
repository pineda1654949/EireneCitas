<?php

namespace App\Notifications;

class RecordatorioDeCita extends NotificacionDeCita
{
    protected function asunto(): string
    {
        return 'Recordatorio de cita';
    }

    protected function introduccion(): string
    {
        return 'Te recordamos que tienes una cita programada para mañana.';
    }
}
