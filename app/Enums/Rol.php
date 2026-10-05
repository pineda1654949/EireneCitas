<?php

namespace App\Enums;

/**
 * Roles del sistema (RF-07, Tabla 7 del documento).
 */
enum Rol: string
{
    case Administrador = 'administrador';
    case Recepcionista = 'recepcionista';
    case Psicologo = 'psicologo';
    case Paciente = 'paciente';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Administrador => 'Administrador',
            self::Recepcionista => 'Recepcionista',
            self::Psicologo => 'Psicologo',
            self::Paciente => 'Paciente',
        };
    }

    /**
     * Roles del personal que gestiona citas a nombre de los pacientes.
     */
    public function esPersonalAdministrativo(): bool
    {
        return in_array($this, [self::Administrador, self::Recepcionista], true);
    }
}
