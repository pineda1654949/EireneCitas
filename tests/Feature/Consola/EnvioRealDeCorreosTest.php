<?php

namespace Tests\Feature\Consola;

use App\Models\Cita;
use App\Notifications\CitaCancelada;
use App\Notifications\CitaConfirmada;
use App\Notifications\CitaRegistrada;
use App\Notifications\CitaReprogramada;
use App\Notifications\RecordatorioDeCita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * Prueba de regresion (DEF-006): los correos se envian por la cola real,
 * sin Notification::fake(). Detecta errores que solo aparecen al serializar
 * y deserializar la notificacion, como ocurre en produccion.
 */
class EnvioRealDeCorreosTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    /**
     * @return array<string, array{class-string}>
     */
    public static function notificaciones(): array
    {
        return [
            'registrada' => [CitaRegistrada::class],
            'confirmada' => [CitaConfirmada::class],
            'reprogramada' => [CitaReprogramada::class],
            'cancelada' => [CitaCancelada::class],
            'recordatorio' => [RecordatorioDeCita::class],
        ];
    }

    /**
     * @param  class-string  $clase
     */
    #[DataProvider('notificaciones')]
    public function test_la_notificacion_sobrevive_a_la_serializacion_de_la_cola(string $clase): void
    {
        $cita = Cita::factory()->create();

        $copia = unserialize(serialize(new $clase($cita)));

        $this->assertTrue($copia->cita->is($cita));
    }

    public function test_registrar_una_cita_envia_los_correos_de_verdad(): void
    {
        config(['queue.default' => 'sync', 'mail.default' => 'array']);
        Event::fake([MessageSent::class]);

        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();

        $this->actingAs($usuario)
            ->post(route('citas.store'), [
                'especialidad_id' => $especialidad->id,
                'psicologo_id' => $psicologo->id,
                'fecha' => $this->proximoLunes(),
                'hora' => '10:00',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        // Un correo al paciente y otro al psicologo.
        Event::assertDispatchedTimes(MessageSent::class, 2);
    }
}
