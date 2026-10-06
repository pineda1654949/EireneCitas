<?php

namespace Tests\Feature\Citas;

use App\Enums\EstadoCita;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Paciente;
use App\Models\Promocion;
use App\Models\User;
use App\Notifications\CitaRegistrada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * RF-01: registro de citas.
 */
class RegistrarCitaTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    /**
     * @return array<string, mixed>
     */
    private function datos(User $psicologo, Especialidad $especialidad, array $extra = []): array
    {
        return [
            'especialidad_id' => $especialidad->id,
            'psicologo_id' => $psicologo->id,
            'fecha' => $this->proximoLunes(),
            'hora' => '10:00',
            'motivo_consulta' => 'Ansiedad en el trabajo',
            ...$extra,
        ];
    }

    public function test_el_paciente_ve_el_formulario_de_solicitud(): void
    {
        $this->actingAs($this->usuarioPaciente())
            ->get(route('citas.create'))
            ->assertOk()
            ->assertSee('Solicitar cita')
            ->assertDontSee('name="paciente_id"', false);
    }

    public function test_el_paciente_registra_una_cita_para_si_mismo(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();

        $respuesta = $this->actingAs($usuario)->post(route('citas.store'), $this->datos($psicologo, $especialidad));

        $cita = Cita::sole();
        $respuesta->assertRedirect(route('citas.show', $cita))->assertSessionHas('status');

        $this->assertSame($this->fichaDe($usuario)->id, $cita->paciente_id);
        $this->assertSame(EstadoCita::Pendiente, $cita->estado);
        $this->assertSame($usuario->id, $cita->creado_por);
        $this->assertSame('Ansiedad en el trabajo', $cita->motivo_consulta);
    }

    public function test_un_paciente_no_puede_registrar_citas_a_nombre_de_otro(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $otro = Paciente::factory()->create();

        $this->actingAs($usuario)->post(route('citas.store'), $this->datos($psicologo, $especialidad, ['paciente_id' => $otro->id]));

        $this->assertSame($this->fichaDe($usuario)->id, Cita::sole()->paciente_id);
    }

    public function test_la_recepcionista_registra_la_cita_de_un_paciente(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $paciente = Paciente::factory()->create();
        $promocion = Promocion::factory()->create();

        $this->actingAs(User::factory()->recepcionista()->create())
            ->post(route('citas.store'), $this->datos($psicologo, $especialidad, [
                'paciente_id' => $paciente->id,
                'promocion_id' => $promocion->id,
            ]))
            ->assertSessionHasNoErrors();

        $cita = Cita::sole();
        $this->assertSame($paciente->id, $cita->paciente_id);
        $this->assertSame($promocion->id, $cita->promocion_id);
    }

    public function test_el_personal_debe_indicar_el_paciente(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();

        $this->actingAs(User::factory()->administrador()->create())
            ->post(route('citas.store'), $this->datos($psicologo, $especialidad))
            ->assertSessionHasErrors('paciente_id');

        $this->assertDatabaseCount('citas', 0);
    }

    public function test_no_permite_reservar_un_horario_ya_ocupado(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $this->citaPara($psicologo, atributos: ['hora' => '10:00']);

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), $this->datos($psicologo, $especialidad))
            ->assertSessionHasErrors('hora');

        $this->assertDatabaseCount('citas', 1);
    }

    public function test_no_permite_reservar_fuera_del_horario_del_psicologo(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), $this->datos($psicologo, $especialidad, ['hora' => '20:00']))
            ->assertSessionHasErrors('hora');

        $this->assertDatabaseCount('citas', 0);
    }

    public function test_no_permite_un_psicologo_que_no_atiende_la_especialidad(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $otraEspecialidad = Especialidad::factory()->create();

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), $this->datos($psicologo, $otraEspecialidad))
            ->assertSessionHasErrors('psicologo_id');
    }

    public function test_no_permite_reservar_con_un_usuario_que_no_es_psicologo(): void
    {
        [, $especialidad] = $this->psicologoConAgenda();
        $recepcionista = User::factory()->recepcionista()->create();

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), $this->datos($recepcionista, $especialidad))
            ->assertSessionHasErrors('psicologo_id');
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function datosInvalidos(): array
    {
        return [
            'fecha pasada' => [['fecha' => '2026-10-01'], 'fecha'],
            'fecha con formato incorrecto' => [['fecha' => '12/10/2026'], 'fecha'],
            'hora con formato incorrecto' => [['hora' => '10am'], 'hora'],
            'sin especialidad' => [['especialidad_id' => null], 'especialidad_id'],
            'especialidad inexistente' => [['especialidad_id' => 9999], 'especialidad_id'],
            'sin psicologo' => [['psicologo_id' => null], 'psicologo_id'],
            'motivo demasiado largo' => [['motivo_consulta' => str_repeat('a', 2001)], 'motivo_consulta'],
        ];
    }

    /**
     * @param  array<string, mixed>  $cambios
     */
    #[DataProvider('datosInvalidos')]
    public function test_valida_los_datos_de_la_cita(array $cambios, string $campo): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), $this->datos($psicologo, $especialidad, $cambios))
            ->assertSessionHasErrors($campo);

        $this->assertDatabaseCount('citas', 0);
    }

    public function test_no_acepta_una_promocion_inactiva(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $promocion = Promocion::factory()->inactiva()->create();

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), $this->datos($psicologo, $especialidad, ['promocion_id' => $promocion->id]))
            ->assertSessionHasErrors('promocion_id');
    }

    public function test_notifica_al_paciente_y_al_psicologo(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();

        $this->actingAs($usuario)->post(route('citas.store'), $this->datos($psicologo, $especialidad));

        Notification::assertSentTo($this->fichaDe($usuario), CitaRegistrada::class);
        Notification::assertSentTo($psicologo, CitaRegistrada::class);
    }

    public function test_un_psicologo_no_puede_registrar_citas(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();

        $this->actingAs($psicologo)->get(route('citas.create'))->assertForbidden();
        $this->actingAs($psicologo)->post(route('citas.store'), $this->datos($psicologo, $especialidad))->assertForbidden();
    }

    public function test_un_invitado_es_enviado_al_login(): void
    {
        $this->get(route('citas.create'))->assertRedirect(route('login'));
    }
}
