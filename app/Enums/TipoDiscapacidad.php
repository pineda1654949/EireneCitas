<?php

namespace App\Enums;

/**
 * Tipo de discapacidad reportada por el paciente (accesibilidad en la atencion).
 */
enum TipoDiscapacidad: string
{
    case Fisica = 'fisica';
    case Visual = 'visual';
    case Auditiva = 'auditiva';
    case Intelectual = 'intelectual';
    case Psicosocial = 'psicosocial';
    case Otra = 'otra';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Fisica => 'Física',
            self::Visual => 'Visual',
            self::Auditiva => 'Auditiva',
            self::Intelectual => 'Intelectual',
            self::Psicosocial => 'Psicosocial',
            self::Otra => 'Otra',
        };
    }
}
