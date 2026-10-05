<?php

namespace App\Services;

use App\Enums\EstadoDerivacion;
use App\Exceptions\ReglaDeNegocioException;
use App\Models\Derivacion;
use App\Models\Paciente;
use App\Models\User;
use App\Notifications\DerivacionRecibida;
use App\Notifications\DerivacionRespondida;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * RF-05: derivacion de pacientes. La administracion deriva al paciente a un
 * psicologo segun sus sintomas; el psicologo la acepta o la rechaza con motivo.
 */
class DerivacionService
{
    public function derivar(Paciente $paciente, int $psicologoId, ?int $especialidadId, ?string $observaciones, User $derivador): Derivacion
    {
        $psicologo = User::psicologos()->activos()->find($psicologoId);

        if (! $psicologo) {
            throw new ReglaDeNegocioException('El psicólogo seleccionado no está disponible.', 'psicologo_id');
        }

        if ($especialidadId && ! $psicologo->especialidades()->whereKey($especialidadId)->exists()) {
            throw new ReglaDeNegocioException('El psicólogo seleccionado no atiende esa especialidad.', 'especialidad_id');
        }

        if ($paciente->derivaciones()->where('estado', EstadoDerivacion::Pendiente->value)->exists()) {
            throw new ReglaDeNegocioException('El paciente ya tiene una derivación pendiente de respuesta.', 'psicologo_id');
        }

        $derivacion = Derivacion::create([
            'paciente_id' => $paciente->id,
            'psicologo_id' => $psicologo->id,
            'especialidad_id' => $especialidadId,
            'derivado_por' => $derivador->id,
            'observaciones' => $observaciones,
            'estado' => EstadoDerivacion::Pendiente,
        ]);

        $this->notificar($psicologo, new DerivacionRecibida($derivacion));

        return $derivacion;
    }

    /**
     * Al aceptar, el psicologo queda asignado al paciente.
     */
    public function aceptar(Derivacion $derivacion): Derivacion
    {
        $this->exigirPendiente($derivacion);

        DB::transaction(function () use ($derivacion) {
            $derivacion->update([
                'estado' => EstadoDerivacion::Aceptada,
                'respondida_at' => now(),
            ]);

            $derivacion->paciente->update(['psicologo_id' => $derivacion->psicologo_id]);
        });

        $this->notificarRespuesta($derivacion);

        return $derivacion;
    }

    public function rechazar(Derivacion $derivacion, string $motivo): Derivacion
    {
        $this->exigirPendiente($derivacion);

        $derivacion->update([
            'estado' => EstadoDerivacion::Rechazada,
            'motivo_rechazo' => $motivo,
            'respondida_at' => now(),
        ]);

        $this->notificarRespuesta($derivacion);

        return $derivacion;
    }

    private function exigirPendiente(Derivacion $derivacion): void
    {
        if (! $derivacion->estaPendiente()) {
            throw new ReglaDeNegocioException('Esta derivación ya fue respondida.', 'derivacion');
        }
    }

    private function notificarRespuesta(Derivacion $derivacion): void
    {
        $derivacion->loadMissing('derivadoPor');

        if ($derivacion->derivadoPor) {
            $this->notificar($derivacion->derivadoPor, new DerivacionRespondida($derivacion));
        }
    }

    private function notificar(User $destinatario, Notification $notificacion): void
    {
        try {
            $destinatario->notify($notificacion);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
