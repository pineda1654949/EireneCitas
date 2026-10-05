<?php

namespace Tests\Feature\Citas;

use App\Enums\EstadoCita;
use App\Models\Pago;
use App\Models\Reprogramacion;
use App\Models\User;
use App\Notifications\CitaCancelada;
use App\Notifications\CitaConfirmada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * RF-03 (cancelacion) y RF-04 (confirmacion de citas).
 */
class CancelarYConfirmarCitaTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_el_paciente_cancela_su_cita_y_queda_registro(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $cita = $this->citaPara($psicologo, $this->fichaDe($usuario));

        $this->actingAs($usuario)->get(route('citas.cancelar.form', $cita))->assertOk();
        $this->actingAs($usuario)
            ->put(route('citas.cancelar', $cita), ['motivo' => 'Ya no lo necesito'])
            ->assertRedirect(route('citas.show', $cita));

        $this->assertSame(EstadoCita::Cancelada, $cita->refresh()->estado);
        $this->assertDatabaseHas('reprogramaciones', [
            'cita_id' => $cita->id,
            'tipo' => Reprogramacion::TIPO_CANCELACION,
            'motivo' => 'Ya no lo necesito',
        ]);
        Notification::assertSentTo($psicologo, CitaCancelada::class);
    }

    public function test_cancelar_libera_el_horario(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['hora' => '10:00']);
        $recepcion = User::factory()->recepcionista()->create();

        $this->actingAs($recepcion)->put(route('citas.cancelar', $cita));

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), [
                'especialidad_id' => $especialidad->id,
                'psicologo_id' => $psicologo->id,
                'fecha' => $this->proximoLunes(),
                'hora' => '10:00',
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_no_se_cancela_dos_veces_ni_una_cita_atendida(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $recepcion = User::factory()->recepcionista()->create();

        foreach ([EstadoCita::Cancelada, EstadoCita::Atendida] as $estado) {
            $cita = $this->citaPara($psicologo, atributos: ['estado' => $estado]);

            $this->actingAs($recepcion)->put(route('citas.cancelar', $cita))->assertSessionHasErrors('estado');
            $this->actingAs($recepcion)->get(route('citas.cancelar.form', $cita))->assertRedirect(route('citas.show', $cita));
            $this->assertSame($estado, $cita->refresh()->estado);
        }

        $this->assertDatabaseCount('reprogramaciones', 0);
    }

    public function test_un_paciente_no_cancela_citas_ajenas(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);

        $this->actingAs($this->usuarioPaciente())->put(route('citas.cancelar', $cita))->assertForbidden();
        $this->assertSame(EstadoCita::Pendiente, $cita->refresh()->estado);
    }

    public function test_no_se_confirma_una_cita_sin_pago_validado(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);
        Pago::factory()->create(['cita_id' => $cita->id]); // pendiente, no validado

        $this->actingAs(User::factory()->recepcionista()->create())
            ->put(route('citas.confirmar', $cita))
            ->assertSessionHasErrors('estado');

        $this->assertSame(EstadoCita::Pendiente, $cita->refresh()->estado);
        Notification::assertNothingSent();
    }

    public function test_se_confirma_con_pago_validado_y_se_notifica(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);
        Pago::factory()->confirmado()->create(['cita_id' => $cita->id]);

        $this->actingAs(User::factory()->administrador()->create())
            ->put(route('citas.confirmar', $cita))
            ->assertSessionHasNoErrors();

        $this->assertSame(EstadoCita::Confirmada, $cita->refresh()->estado);
        Notification::assertSentTo($cita->paciente, CitaConfirmada::class);
    }

    public function test_una_cita_reprogramada_con_pago_se_puede_confirmar(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['estado' => EstadoCita::Reprogramada]);
        Pago::factory()->confirmado()->create(['cita_id' => $cita->id]);

        $this->actingAs(User::factory()->recepcionista()->create())->put(route('citas.confirmar', $cita));

        $this->assertSame(EstadoCita::Confirmada, $cita->refresh()->estado);
    }

    public function test_no_se_confirma_una_cita_cancelada_aunque_tenga_pago(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['estado' => EstadoCita::Cancelada]);
        Pago::factory()->confirmado()->create(['cita_id' => $cita->id]);

        $this->actingAs(User::factory()->recepcionista()->create())
            ->put(route('citas.confirmar', $cita))
            ->assertSessionHasErrors('estado');

        $this->assertSame(EstadoCita::Cancelada, $cita->refresh()->estado);
    }

    public function test_solo_el_personal_puede_confirmar(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $cita = $this->citaPara($psicologo, $this->fichaDe($usuario));
        Pago::factory()->confirmado()->create(['cita_id' => $cita->id]);

        $this->actingAs($usuario)->put(route('citas.confirmar', $cita))->assertForbidden();
        $this->actingAs($psicologo)->put(route('citas.confirmar', $cita))->assertForbidden();
    }
}
