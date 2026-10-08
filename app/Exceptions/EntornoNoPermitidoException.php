<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Se lanza cuando una tarea de soporte (seeders de carga, datos de prueba)
 * se intenta ejecutar en un entorno donde no corresponde, como produccion.
 */
class EntornoNoPermitidoException extends RuntimeException
{
    public static function enProduccion(string $tarea): self
    {
        return new self("{$tarea} no debe ejecutarse en produccion.");
    }
}
