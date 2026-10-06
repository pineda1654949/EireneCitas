<?php

namespace App\Notifications;

use App\Models\Derivacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso al psicologo de que tiene una derivacion por responder (RF-05).
 */
class DerivacionRecibida extends Notification implements ShouldQueue
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
        $derivacion = $this->derivacion->loadMissing(['paciente', 'especialidad']);

        return (new MailMessage)
            ->subject('Nueva derivación de paciente - '.config('eirene.clinica.nombre'))
            ->greeting('Hola:')
            ->line('La clínica te derivó un paciente. Revisa el caso y acéptalo o recházalo.')
            ->line('**Paciente:** '.$derivacion->paciente->nombre_completo)
            ->line('**Especialidad:** '.($derivacion->especialidad->nombre ?? 'Sin especificar'))
            ->action('Responder derivación', route('psicologo.derivaciones.index'))
            ->salutation('Atentamente, '.config('eirene.clinica.nombre'));
    }
}
