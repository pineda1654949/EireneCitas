<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Se lanza cuando una operacion viola una regla de negocio de la clinica
 * (horario ocupado, limite de reprogramaciones, cita sin pago, etc.).
 * Se convierte en un error de formulario en bootstrap/app.php.
 */
class ReglaDeNegocioException extends RuntimeException
{
    public function __construct(string $mensaje, public readonly string $campo = 'general')
    {
        parent::__construct($mensaje);
    }
}
