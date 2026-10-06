<?php

namespace App\Notifications;

use App\Enums\EstadoDerivacion;
use App\Models\Derivacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso a quien derivo al paciente de que el psicologo respondio (RF-05).
 */
class DerivacionRespondida extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Derivacion $derivacion)
    {
        $this->afterCommit();
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $derivacion = $this->derivacion->loadMissing(['paciente', 'psicologo']);
        $aceptada = $derivacion->estado === EstadoDerivacion::Aceptada;

        $mensaje = (new MailMessage)
            ->subject('Derivación '.mb_strtolower($derivacion->estado->etiqueta()).' - '.config('eirene.clinica.nombre'))
            ->greeting('Hola:')
            ->line($derivacion->psicologo->nombre_completo.($aceptada ? ' aceptó' : ' rechazó').' la derivación de '.$derivacion->paciente->nombre_completo.'.');

        if (! $aceptada && $derivacion->motivo_rechazo) {
            $mensaje->line('**Motivo:** '.$derivacion->motivo_rechazo);
        }

        return $mensaje
            ->action('Ver derivaciones', route('admin.derivaciones.index'))
            ->salutation('Atentamente, '.config('eirene.clinica.nombre'));
    }
}
