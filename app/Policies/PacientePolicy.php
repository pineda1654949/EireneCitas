<?php

namespace App\Policies;

use App\Models\Paciente;
use App\Models\User;

class PacientePolicy
{
    /**
     * RF-06: la historia clinica es informacion sensible de salud. Solo la
     * consulta un psicologo que atiende (o atendio) al paciente.
     */
    public function verHistorial(User $usuario, Paciente $paciente): bool
    {
        if (! $usuario->esPsicologo()) {
            return false;
        }

        // Psicologo asignado por una derivacion aceptada (RF-05) o que lo atiende.
        return $paciente->psicologo_id === $usuario->id
            || $paciente->citas()->where('psicologo_id', $usuario->id)->exists();
    }
}
