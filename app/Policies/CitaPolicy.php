<?php

namespace App\Policies;

use App\Models\Cita;
use App\Models\User;

/**
 * Quien puede hacer que con una cita (RF-07). Las reglas de negocio
 * (limite de reprogramaciones, pago validado, etc.) viven en CitaService.
 */
class CitaPolicy
{
    public function view(User $usuario, Cita $cita): bool
    {
        if ($usuario->esPersonalAdministrativo()) {
            return true;
        }

        if ($usuario->esPsicologo()) {
            return $cita->psicologo_id === $usuario->id;
        }

        return $usuario->esPaciente()
            && $usuario->pacienteFicha !== null
            && $cita->paciente_id === $usuario->pacienteFicha->id;
    }

    public function create(User $usuario): bool
    {
        return $usuario->esPersonalAdministrativo()
            || ($usuario->esPaciente() && $usuario->pacienteFicha !== null);
    }

    public function reprogramar(User $usuario, Cita $cita): bool
    {
        return $this->view($usuario, $cita);
    }

    public function cancelar(User $usuario, Cita $cita): bool
    {
        return $this->view($usuario, $cita);
    }

    public function confirmar(User $usuario): bool
    {
        return $usuario->esPersonalAdministrativo();
    }

    public function gestionarPagos(User $usuario): bool
    {
        return $usuario->esPersonalAdministrativo();
    }

    /**
     * Registrar un pago con su voucher: el personal o el propio paciente
     * (reemplaza el envio del comprobante por WhatsApp del AS-IS).
     */
    public function reportarPago(User $usuario, Cita $cita): bool
    {
        return $usuario->esPersonalAdministrativo()
            || ($usuario->esPaciente() && $this->view($usuario, $cita));
    }

    /**
     * RF-06: solo el psicologo asignado registra la sesion.
     */
    public function atender(User $usuario, Cita $cita): bool
    {
        return $usuario->esPsicologo() && $cita->psicologo_id === $usuario->id;
    }
}
