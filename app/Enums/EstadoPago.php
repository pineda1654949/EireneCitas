<?php

namespace App\Enums;

enum EstadoPago: string
{
    case Pendiente = 'pendiente';
    case Confirmado = 'confirmado';
    case Rechazado = 'rechazado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Confirmado => 'Confirmado',
            self::Rechazado => 'Rechazado',
        };
    }

    public function tono(): string
    {
        return match ($this) {
            self::Pendiente => 'amber',
            self::Confirmado => 'emerald',
            self::Rechazado => 'rose',
        };
    }
}
