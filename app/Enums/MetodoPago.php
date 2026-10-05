<?php

namespace App\Enums;

enum MetodoPago: string
{
    case Tarjeta = 'tarjeta';
    case YapePlin = 'yape_plin';
    case Transferencia = 'transferencia';
    case Efectivo = 'efectivo';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Tarjeta => 'Tarjeta',
            self::YapePlin => 'Yape / Plin',
            self::Transferencia => 'Transferencia',
            self::Efectivo => 'Efectivo',
        };
    }
}
