<?php

namespace Tests\Fase6;

use App\Enums\EstadoDerivacion;
use App\Models\Cita;
use App\Models\Derivacion;
use App\Models\Horario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * Fase 6 - Unit Testing. Vistas Blade (CP-UT-39 a CP-UT-48, en lugar de los
 * componentes React del plan original) y derivacion RF-05 (CP-UT-49 a 51).
 */
class VistasYDerivacionTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_CP_UT_39_vista_citas_crear_muestra_el_formulario(): void
    {
        $this->actingAs($this->usuarioPaciente())
            ->get(route('citas.create'))
            ->assertOk()
            ->assertSee('name="especialidad_id"', false)
            ->assertSee('name="psicologo_id"', false)
            ->assertSee('name="fecha"', false)
            ->assertSee('data-horas', false)
            ->assertSee('name="_token"', false);
    }

    public function test_CP_UT_40_formulario_con_datos_validos_se_procesa(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), [
                'especialidad_id' => $especialidad->id, 'psicologo_id' => $psicologo->id,
                'fecha' => $this->proximoLunes(), 'hora' => '10:00',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('citas.show', Cita::sole()));
    }

    public function test_CP_UT_41_formulario_vacio_muestra_mensajes_de_error(): void
    {
        $this->actingAs($this->usuarioPaciente())
            ->from(route('citas.create'))
            ->followingRedirects()
            ->post(route('citas.store'), [])
            ->assertOk()
            ->assertSee('El campo especialidad es obligatorio.')
            ->assertSee('El campo psicólogo es obligatorio.')
            ->assertSee('El campo fecha es obligatorio.');
    }

    public function test_CP_UT_42_error_de_validacion_conserva_lo_escrito(): void
    {
        $this->actingAs(User::factory()->recepcionista()->create())
            ->from(route('admin.pacientes.create'))
            ->followingRedirects()
            ->post(route('admin.pacientes.store'), ['nombres' => 'Ana Lucia', 'apellidos' => 'Quispe', 'telefono' => '12345'])
            ->assertSee('value="Ana Lucia"', false)
            ->assertSee('value="Quispe"', false)
            ->assertSee('value="12345"', false);
    }

    public function test_CP_UT_43_la_agenda_diferencia_horas_libres_ocupadas_y_bloqueadas(): void
    {
        [$psicologo] = $this->psicologoConAgenda(); // lunes 09:00-13:00
        Horario::factory()->inactivo()->create(['psicologo_id' => $psicologo->id, 'dia_semana' => 1, 'hora_inicio' => '15:00', 'hora_fin' => '16:00']);
        $this->citaPara($psicologo, atributos: ['hora' => '10:00']);

        $this->actingAs($this->usuarioPaciente())
            ->getJson(route('api.agenda-del-dia', ['psicologo_id' => $psicologo->id, 'fecha' => $this->proximoLunes()]))
            ->assertOk()
            ->assertExactJson([
                ['hora' => '09:00', 'estado' => 'libre'],
                ['hora' => '10:00', 'estado' => 'ocupado'],
                ['hora' => '11:00', 'estado' => 'libre'],
                ['hora' => '12:00', 'estado' => 'libre'],
                ['hora' => '15:00', 'estado' => 'bloqueado'],
            ]);

        $this->get(route('citas.create'))->assertSee('data-horas-leyenda', false)->assertSee('Ocupado');
    }

    public function test_CP_UT_44_una_hora_ocupada_no_se_puede_seleccionar(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $this->citaPara($psicologo, atributos: ['hora' => '10:00']);

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), [
                'especialidad_id' => $especialidad->id, 'psicologo_id' => $psicologo->id,
                'fecha' => $this->proximoLunes(), 'hora' => '10:00',
            ])
            ->assertSessionHasErrors('hora');
    }

    public function test_CP_UT_45_una_hora_disponible_se_puede_seleccionar(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), [
                'especialidad_id' => $especialidad->id, 'psicologo_id' => $psicologo->id,
                'fecha' => $this->proximoLunes(), 'hora' => '11:00',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('11:00', Cita::sole()->hora_corta);
    }

    public function test_CP_UT_46_reprogramar_con_contador_en_3_muestra_aviso(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $cita = $this->citaPara($psicologo, $this->fichaDe($usuario), ['numero_reprogramaciones' => 3]);

        $this->actingAs($usuario)
            ->get(route('citas.show', $cita))
            ->assertSee('Se alcanzó el límite de 3 reprogramaciones')
            ->assertDontSee(route('citas.reprogramar.form', $cita));

        $this->actingAs($usuario)
            ->get(route('citas.reprogramar.form', $cita))
            ->assertRedirect(route('citas.show', $cita));
    }

    public function test_CP_UT_47_dashboard_muestra_metricas(): void
    {
        Cita::factory()->count(2)->create();
        Cita::factory()->confirmada()->create();

        $this->actingAs(User::factory()->administrador()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Pacientes registrados')
            ->assertSee('Psicólogos activos')
            ->assertSee('Citas pendientes')
            ->assertSee('Ingresos del mes')
            ->assertViewHas('citasPorEstado', fn ($porEstado) => (int) $porEstado['pendiente'] === 2 && (int) $porEstado['confirmada'] === 1);
    }

    public function test_CP_UT_48_logout_cierra_la_sesion_y_redirige_al_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_CP_UT_49_rechazo_de_derivacion_sin_motivo_da_error(): void
    {
        $derivacion = Derivacion::factory()->create();

        $this->actingAs($derivacion->psicologo)
            ->put(route('psicologo.derivaciones.rechazar', $derivacion), ['motivo_rechazo' => ''])
            ->assertSessionHasErrors(['motivo_rechazo' => 'El campo motivo del rechazo es obligatorio.']);

        $this->assertSame(EstadoDerivacion::Pendiente, $derivacion->fresh()?->estado);
    }

    public function test_CP_UT_50_aceptacion_de_derivacion_asigna_el_paciente(): void
    {
        $derivacion = Derivacion::factory()->create();

        $this->actingAs($derivacion->psicologo)
            ->put(route('psicologo.derivaciones.aceptar', $derivacion))
            ->assertSessionHasNoErrors();

        $this->assertSame(EstadoDerivacion::Aceptada, $derivacion->fresh()?->estado);
        $this->assertSame($derivacion->psicologo_id, $derivacion->paciente->fresh()?->psicologo_id);
    }

    public function test_CP_UT_51_un_psicologo_distinto_no_puede_responder_la_derivacion(): void
    {
        $derivacion = Derivacion::factory()->create();
        $otro = User::factory()->psicologo()->create();

        $this->actingAs($otro)->put(route('psicologo.derivaciones.aceptar', $derivacion))->assertForbidden();
        $this->actingAs($otro)
            ->put(route('psicologo.derivaciones.rechazar', $derivacion), ['motivo_rechazo' => 'No me corresponde este caso'])
            ->assertForbidden();

        $this->assertSame(EstadoDerivacion::Pendiente, $derivacion->fresh()?->estado);
    }
}
