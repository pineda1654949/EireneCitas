<?php

namespace App\Policies;

use App\Models\Derivacion;
use App\Models\User;

class DerivacionPolicy
{
    /**
     * Solo el psicologo al que se derivo puede aceptar o rechazar (CP-UT-51).
     */
    public function responder(User $usuario, Derivacion $derivacion): bool
    {
        return $usuario->esPsicologo() && $derivacion->psicologo_id === $usuario->id;
    }
}
