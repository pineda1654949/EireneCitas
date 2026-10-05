<?php

namespace Tests\Fase7;

use App\Enums\EstadoAtencion;
use App\Enums\EstadoCita;
use App\Enums\EstadoDerivacion;
use App\Models\Cita;
use App\Models\Derivacion;
use App\Models\HistorialClinico;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\User;
use App\Notifications\CitaConfirmada;
use App\Notifications\DerivacionRecibida;
use App\Notifications\DerivacionRespondida;
use App\Notifications\RecordatorioDeCita;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mockery;
use RuntimeException;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * Fase 7 - Integration Testing. Derivacion y expediente (CP-IT-23 a 29),
 * seguridad (CP-IT-30 a 35) y notificaciones (CP-IT-36 a 38).
 */
class DerivacionSeguridadYNotificacionesTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_CP_IT_23_derivar_un_paciente_notifica_al_psicologo(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $paciente = Paciente::factory()->create();

        $this->actingAs(User::factory()->recepcionista()->create())
            ->post(route('admin.pacientes.derivar', $paciente), [
                'psicologo_id' => $psicologo->id, 'especialidad_id' => $especialidad->id, 'observaciones' => 'Cuadro de ansiedad',
            ])
            ->assertSessionHasNoErrors();

        $derivacion = Derivacion::sole();
        $this->assertSame(EstadoDerivacion::Pendiente, $derivacion->estado);
        Notification::assertSentTo($psicologo, DerivacionRecibida::class);
    }

    public function test_CP_IT_24_aceptar_derivacion_asigna_el_paciente_y_avisa_a_la_clinica(): void
    {
        $derivacion = Derivacion::factory()->create();

        $this->actingAs($derivacion->psicologo)->put(route('psicologo.derivaciones.aceptar', $derivacion))->assertRedirect();

        $this->assertSame($derivacion->psicologo_id, $derivacion->paciente->fresh()?->psicologo_id);
        Notification::assertSentTo($derivacion->derivadoPor, DerivacionRespondida::class);
    }

    public function test_CP_IT_25_rechazar_derivacion_guarda_el_motivo_y_avisa_a_la_clinica(): void
    {
        $derivacion = Derivacion::factory()->create();

        $this->actingAs($derivacion->psicologo)
            ->put(route('psicologo.derivaciones.rechazar', $derivacion), ['motivo_rechazo' => 'No atiendo terapia infantil'])
            ->assertSessionHasNoErrors();

        $derivacion->refresh();
        $this->assertSame(EstadoDerivacion::Rechazada, $derivacion->estado);
        $this->assertSame('No atiendo terapia infantil', $derivacion->motivo_rechazo);
        $this->assertNull($derivacion->paciente->psicologo_id);
        Notification::assertSentTo($derivacion->derivadoPor, DerivacionRespondida::class);

        // Una derivacion ya respondida no se puede volver a responder.
        $this->actingAs($derivacion->psicologo)->put(route('psicologo.derivaciones.aceptar', $derivacion))->assertSessionHasErrors('derivacion');
    }

    public function test_CP_IT_26_registrar_historial(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['fecha' => now()->toDateString(), 'hora' => '09:00']);

        $this->actingAs($psicologo)
            ->post(route('psicologo.historial.store', $cita), ['notas_sesion' => 'Primera sesion: buena disposicion.', 'avance' => 'Inicial'])
            ->assertRedirect(route('citas.show', $cita));

        $this->assertSame('Primera sesion: buena disposicion.', HistorialClinico::sole()->notas_sesion);
        $this->assertSame(EstadoCita::Atendida, $cita->fresh()?->estado);
        $this->assertSame(EstadoAtencion::EnProceso, $cita->paciente->fresh()?->estado_atencion);
    }

    public function test_CP_IT_27_psicologo_no_ve_el_historial_de_un_paciente_ajeno(): void
    {
        $historial = HistorialClinico::factory()->create();

        $this->actingAs(User::factory()->psicologo()->create())
            ->get(route('psicologo.historial.paciente', $historial->paciente))
            ->assertForbidden();
    }

    public function test_CP_IT_28_cambiar_el_estado_de_atencion_del_paciente(): void
    {
        $paciente = Paciente::factory()->create();

        $this->actingAs(User::factory()->recepcionista()->create())
            ->put(route('admin.pacientes.update', $paciente), [
                'nombres' => $paciente->nombres, 'apellidos' => $paciente->apellidos, 'estado_atencion' => 'finalizado',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(EstadoAtencion::Finalizado, $paciente->fresh()?->estado_atencion);
    }

    public function test_CP_IT_29_estado_de_atencion_invalido_da_error(): void
    {
        $paciente = Paciente::factory()->create();

        $this->actingAs(User::factory()->recepcionista()->create())
            ->put(route('admin.pacientes.update', $paciente), [
                'nombres' => $paciente->nombres, 'apellidos' => $paciente->apellidos, 'estado_atencion' => 'eliminado',
            ])
            ->assertSessionHasErrors('estado_atencion');

        $this->assertSame(EstadoAtencion::Inscripto, $paciente->fresh()?->estado_atencion);
    }

    public function test_CP_IT_30_ruta_protegida_sin_sesion_redirige_al_login(): void
    {
        $this->get(route('admin.pacientes.index'))->assertRedirect(route('login'));
    }

    public function test_CP_IT_31_sesion_manipulada_redirige_al_login(): void
    {
        $this->withUnencryptedCookie(config('session.cookie'), 'sesion-alterada-por-un-atacante')
            ->get(route('admin.pacientes.index'))
            ->assertRedirect(route('login'));

        $this->withCookie(config('session.cookie'), 'eyJpdiI6ImZhbHNvIn0=')
            ->get(route('home'))
            ->assertRedirect(route('login'));
    }

    public function test_CP_IT_32_sesion_expirada_redirige_al_login(): void
    {
        $this->actingAs(User::factory()->administrador()->create())->get(route('home'))->assertOk();

        $this->flushSession();
        $this->app['auth']->forgetGuards();

        $this->get(route('admin.pacientes.index'))->assertRedirect(route('login'));
    }

    public function test_CP_IT_33_psicologo_no_puede_crear_promociones(): void
    {
        $this->actingAs(User::factory()->psicologo()->create())
            ->post(route('admin.promociones.store'), ['nombre' => 'X', 'numero_sesiones' => 1, 'precio' => 10])
            ->assertForbidden();

        $this->assertDatabaseCount('promociones', 0);
    }

    public function test_CP_IT_34_psicologo_no_puede_listar_pacientes(): void
    {
        $this->actingAs(User::factory()->psicologo()->create())->get(route('admin.pacientes.index'))->assertForbidden();
    }

    public function test_CP_IT_35_inyeccion_sql_en_el_dni_no_tiene_efecto(): void
    {
        $existente = Paciente::factory()->create(['nombres' => 'Paciente', 'apellidos' => 'Confidencial']);
        $recepcion = User::factory()->recepcionista()->create();

        $this->actingAs($recepcion)
            ->post(route('admin.pacientes.store'), ['nombres' => 'X', 'apellidos' => 'Y', 'dni' => "1' OR '1'='1"])
            ->assertSessionHasErrors('dni');

        $this->actingAs($recepcion)
            ->get(route('admin.pacientes.index', ['buscar' => "' OR 1=1 --"]))
            ->assertOk()
            ->assertDontSee('Confidencial');

        $this->assertSame(1, Paciente::count());
        $this->assertModelExists($existente);
    }

    public function test_CP_IT_36_confirmar_la_cita_envia_correo_al_paciente_y_al_psicologo(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);
        $pago = Pago::factory()->create(['cita_id' => $cita->id]);

        $this->actingAs(User::factory()->recepcionista()->create())->put(route('pagos.validar', $pago));

        Notification::assertSentTo($cita->paciente, CitaConfirmada::class);
        Notification::assertSentTo($psicologo, CitaConfirmada::class);
    }

    public function test_CP_IT_37_el_recordatorio_24_horas_existe_y_se_programa(): void
    {
        $manana = Cita::factory()->confirmada()->create(['fecha' => now()->addDay()->toDateString()]);

        $this->artisan('citas:enviar-recordatorios')->assertSuccessful();

        Notification::assertSentTo($manana->paciente, RecordatorioDeCita::class);
        $this->artisan('schedule:list')->expectsOutputToContain('citas:enviar-recordatorios')->assertSuccessful();
    }

    public function test_CP_IT_38_un_fallo_del_correo_no_revierte_la_confirmacion(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);
        $pago = Pago::factory()->create(['cita_id' => $cita->id]);

        $correoCaido = Mockery::mock(Dispatcher::class);
        $correoCaido->shouldReceive('send')->andThrow(new RuntimeException('Servidor SMTP no disponible'));
        $this->app->instance(Dispatcher::class, $correoCaido);

        $this->actingAs(User::factory()->recepcionista()->create())
            ->put(route('pagos.validar', $pago))
            ->assertRedirect()
            ->assertSessionHas('status', 'Pago validado y cita confirmada.');

        $this->assertSame(EstadoCita::Confirmada, $cita->fresh()?->estado);
    }
}
