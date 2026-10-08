<?php

namespace App\Models\Concerns;

use App\Enums\EstadoPago;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\User;

/**
 * Reglas de negocio de una cita (reprogramacion, cancelacion y
 * confirmacion). Se separan del modelo para que Cita solo describa
 * sus datos, relaciones y consultas.
 *
 * @mixin Cita
 */
trait AplicaReglasDeCita
{
    public function puedeReprogramarse(): bool
    {
        return $this->numero_reprogramaciones < Cita::maxReprogramaciones()
            && ! $this->estado->estaCerrada();
    }

    /**
     * RN-01: superado el limite, solo el administrador puede autorizar un
     * nuevo cambio.
     */
    public function puedeReprogramarsePor(User $usuario): bool
    {
        if ($this->estado->estaCerrada()) {
            return false;
        }

        return $this->numero_reprogramaciones < Cita::maxReprogramaciones() || $usuario->esAdministrador();
    }

    public function reprogramacionesRestantes(): int
    {
        return max(0, Cita::maxReprogramaciones() - $this->numero_reprogramaciones);
    }

    public function puedeCancelarse(): bool
    {
        return ! $this->estado->estaCerrada();
    }

    public function puedeConfirmarse(): bool
    {
        return $this->estado->admiteConfirmacion() && $this->pagoConfirmado();
    }

    public function pagoConfirmado(): bool
    {
        if ($this->relationLoaded('pagos')) {
            return $this->pagos->contains(fn (Pago $pago) => $pago->estado === EstadoPago::Confirmado);
        }

        return $this->pagos()->where('estado', EstadoPago::Confirmado->value)->exists();
    }
}
