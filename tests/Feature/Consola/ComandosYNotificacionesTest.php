<?php

namespace Tests\Feature\Consola;

use App\Enums\EstadoCita;
use App\Enums\Rol;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use App\Notifications\CitaConfirmada;
use App\Notifications\RecordatorioDeCita;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Tareas automaticas (recordatorios, respaldos), comando de instalacion
 * y contenido de los correos.
 */
class ComandosYNotificacionesTest extends TestCase
{
    use RefreshDatabase;

    public function test_envia_recordatorios_solo_de_las_citas_activas_de_manana(): void
    {
        Notification::fake();
        $manana = now()->addDay()->toDateString();

        $activa = Cita::factory()->confirmada()->create(['fecha' => $manana]);
        $cancelada = Cita::factory()->cancelada()->create(['fecha' => $manana]);
        $otroDia = Cita::factory()->create(['fecha' => now()->addDays(3)->toDateString()]);

        $this->artisan('citas:enviar-recordatorios')
            ->expectsOutputToContain('Recordatorios enviados: 1')
            ->assertSuccessful();

        Notification::assertSentTo($activa->paciente, RecordatorioDeCita::class);
        Notification::assertNotSentTo($cancelada->paciente, RecordatorioDeCita::class);
        Notification::assertNotSentTo($otroDia->paciente, RecordatorioDeCita::class);
    }

    public function test_crea_un_administrador_desde_consola(): void
    {
        $this->artisan('eirene:crear-admin', ['--usuario' => 'direccion', '--password' => 'Direccion2026'])
            ->assertSuccessful();

        $admin = User::where('email', 'direccion')->sole();
        $this->assertSame(Rol::Administrador, $admin->role);
        $this->assertTrue(Hash::check('Direccion2026', $admin->password));
    }

    public function test_el_comando_exige_una_contrasena_segura_y_usuario_unico(): void
    {
        User::factory()->create(['email' => 'direccion']);

        $this->artisan('eirene:crear-admin', ['--usuario' => 'direccion', '--password' => '123'])
            ->assertFailed();

        $this->assertSame(1, User::count());
    }

    public function test_las_tareas_programadas_estan_registradas(): void
    {
        $comandos = collect(app(Schedule::class)->events())->map(fn ($evento) => (string) $evento->command)->implode("\n");

        foreach (['queue:work', 'citas:enviar-recordatorios', 'backup:run', 'backup:clean', 'backup:monitor', 'model:prune'] as $comando) {
            $this->assertStringContainsString($comando, $comandos);
        }
    }

    public function test_el_correo_de_confirmacion_esta_en_espanol_con_los_datos_de_la_cita(): void
    {
        $paciente = Paciente::factory()->create(['nombres' => 'Lucia']);
        $cita = Cita::factory()->create([
            'paciente_id' => $paciente->id,
            'fecha' => '2026-10-12',
            'hora' => '10:00',
            'estado' => EstadoCita::Confirmada,
        ]);

        $correo = (new CitaConfirmada($cita))->toMail($paciente)->render();

        $this->assertStringContainsString('Hola, Lucia', $correo);
        $this->assertStringContainsString('Lunes 12 de octubre de 2026', $correo);
        $this->assertStringContainsString('10:00', $correo);
        $this->assertStringContainsString($cita->psicologo->nombre_completo, $correo);
        // Un paciente sin cuenta web no recibe un enlace al sistema.
        $this->assertStringNotContainsString('Ver detalle de la cita', $correo);
    }

    public function test_un_paciente_sin_correo_no_genera_error_al_notificar(): void
    {
        Notification::fake();
        $paciente = Paciente::factory()->sinCorreo()->create();

        $this->assertNull($paciente->routeNotificationForMail());
        $paciente->notify(new CitaConfirmada(Cita::factory()->create(['paciente_id' => $paciente->id])));

        Notification::assertSentTo($paciente, CitaConfirmada::class);
    }

    public function test_las_notificaciones_se_envian_en_cola(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, new CitaConfirmada(Cita::factory()->create()));
    }
}
