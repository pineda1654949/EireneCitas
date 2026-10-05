<?php

namespace App\Notifications;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Base de los correos sobre una cita. Se encolan (no retrasan la respuesta
 * al usuario) y se despachan solo despues de confirmar la transaccion.
 */
abstract class NotificacionDeCita extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Sin "readonly": la cola deserializa la notificacion desde la subclase
     * (CitaRegistrada, etc.) y PHP no permite inicializar alli una propiedad
     * readonly declarada en esta clase base (DEF-006).
     */
    public function __construct(public Cita $cita)
    {
        $this->afterCommit();
    }

    abstract protected function asunto(): string;

    abstract protected function introduccion(): string;

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $cita = $this->cita->loadMissing(['psicologo', 'paciente', 'especialidad']);

        $mensaje = (new MailMessage)
            ->subject($this->asunto().' - '.config('eirene.clinica.nombre'))
            ->greeting('Hola, '.$this->nombreDestinatario($notifiable).':')
            ->line($this->introduccion())
            ->line('**Fecha:** '.ucfirst($cita->fecha->isoFormat('dddd D [de] MMMM [de] YYYY')))
            ->line('**Hora:** '.$cita->hora_corta)
            ->line('**Psicologo(a):** '.$cita->psicologo->nombre_completo)
            ->line('**Paciente:** '.$cita->paciente->nombre_completo);

        if ($cita->especialidad) {
            $mensaje->line('**Especialidad:** '.$cita->especialidad->nombre);
        }

        if ($this->tieneCuenta($notifiable)) {
            $mensaje->action('Ver detalle de la cita', route('citas.show', $cita));
        }

        return $mensaje->salutation('Atentamente, '.config('eirene.clinica.nombre'));
    }

    private function nombreDestinatario(object $notifiable): string
    {
        return match (true) {
            $notifiable instanceof User => $notifiable->name,
            $notifiable instanceof Paciente => $notifiable->nombres,
            default => 'estimado(a)',
        };
    }

    private function tieneCuenta(object $notifiable): bool
    {
        return $notifiable instanceof User
            || ($notifiable instanceof Paciente && $notifiable->user_id !== null);
    }
}
