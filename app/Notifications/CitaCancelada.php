<?php

namespace App\Notifications;

class CitaCancelada extends NotificacionDeCita
{
    protected function asunto(): string
    {
        return 'Cita cancelada';
    }

    protected function introduccion(): string
    {
        return 'La siguiente cita fue cancelada. Si fue un error, comunícate con la clínica.';
    }
}
