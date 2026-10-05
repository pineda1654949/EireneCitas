<?php

namespace App\Enums;

/**
 * Ciclo de vida del paciente en la clinica (HU-12).
 */
enum EstadoAtencion: string
{
    case Inscripto = 'inscripto';
    case EnProceso = 'en_proceso';
    case Finalizado = 'finalizado';
    case Cancelado = 'cancelado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Inscripto => 'Inscripto',
            self::EnProceso => 'En proceso',
            self::Finalizado => 'Finalizado',
            self::Cancelado => 'Cancelado',
        };
    }

    public function tono(): string
    {
        return match ($this) {
            self::Inscripto => 'amber',
            self::EnProceso => 'sky',
            self::Finalizado => 'emerald',
            self::Cancelado => 'rose',
        };
    }
}
