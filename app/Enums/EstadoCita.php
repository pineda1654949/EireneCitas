<?php

namespace App\Enums;

enum EstadoCita: string
{
    case Pendiente = 'pendiente';       // creada, a la espera de pago/confirmacion
    case Confirmada = 'confirmada';     // pago validado y cita confirmada (RF-04)
    case Atendida = 'atendida';         // sesion realizada, con historial clinico
    case Cancelada = 'cancelada';
    case Reprogramada = 'reprogramada'; // movida a una nueva fecha/hora

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Confirmada => 'Confirmada',
            self::Atendida => 'Atendida',
            self::Cancelada => 'Cancelada',
            self::Reprogramada => 'Reprogramada',
        };
    }

    /**
     * Tono visual del badge en la interfaz.
     */
    public function tono(): string
    {
        return match ($this) {
            self::Pendiente => 'amber',
            self::Confirmada => 'emerald',
            self::Atendida => 'sky',
            self::Cancelada => 'rose',
            self::Reprogramada => 'violet',
        };
    }

    /**
     * Estados en los que la cita sigue ocupando el horario del psicologo.
     *
     * @return list<self>
     */
    public static function activos(): array
    {
        return [self::Pendiente, self::Confirmada, self::Reprogramada];
    }

    /**
     * @return list<string>
     */
    public static function valoresActivos(): array
    {
        return array_map(fn (self $estado) => $estado->value, self::activos());
    }

    public function estaActiva(): bool
    {
        return in_array($this, self::activos(), true);
    }

    /**
     * Una cita puede confirmarse (o recibir pagos) mientras no este cerrada.
     */
    public function admiteConfirmacion(): bool
    {
        return in_array($this, [self::Pendiente, self::Reprogramada], true);
    }

    public function estaCerrada(): bool
    {
        return in_array($this, [self::Cancelada, self::Atendida], true);
    }
}
