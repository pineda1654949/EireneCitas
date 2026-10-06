<?php

namespace App\Enums;

/**
 * Estado de una derivacion de paciente a psicologo (RF-05).
 */
enum EstadoDerivacion: string
{
    case Pendiente = 'pendiente';
    case Aceptada = 'aceptada';
    case Rechazada = 'rechazada';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Aceptada => 'Aceptada',
            self::Rechazada => 'Rechazada',
        };
    }

    public function tono(): string
    {
        return match ($this) {
            self::Pendiente => 'amber',
            self::Aceptada => 'emerald',
            self::Rechazada => 'rose',
        };
    }
}
