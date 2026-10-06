<?php

namespace Tests\Fase6;

use App\Enums\EstadoCita;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * Fase 6 - Unit Testing. Reglas de negocio RN-01, RN-02 y RN-03
 * (CP-UT-01 a CP-UT-09). Proyecto EireneCitas sobre Laravel 12.
 */
class ReglasDeNegocioTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    private User $recepcion;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->recepcion = User::factory()->recepcionista()->create();
    }

    public function test_CP_UT_01_rn01_bloquea_el_cuarto_cambio(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['numero_reprogramaciones' => 3]);

        $this->actingAs($this->recepcion)
            ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '12:00', 'motivo' => 'Prueba del limite'])
            ->assertSessionHasErrors('fecha');

        $this->assertSame(3, $cita->fresh()?->numero_reprogramaciones);
    }

    public function test_CP_UT_02_rn01_acepta_el_tercer_cambio(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['numero_reprogramaciones' => 2, 'hora' => '10:00']);

        $this->actingAs($this->recepcion)
            ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '12:00', 'motivo' => 'Pedido del paciente'])
            ->assertSessionHasNoErrors();

        $this->assertSame(3, $cita->fresh()?->numero_reprogramaciones);
    }

    public function test_CP_UT_03_rn01_exige_motivo_al_reprogramar(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['hora' => '10:00']);

        $this->actingAs($this->recepcion)
            ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '12:00'])
            ->assertSessionHasErrors(['motivo' => 'El campo motivo es obligatorio.']);

        $this->assertSame(0, $cita->fresh()?->numero_reprogramaciones);
    }

    public function test_CP_UT_04_rn02_no_confirma_con_pago_pendiente(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);
        Pago::factory()->create(['cita_id' => $cita->id]);

        $this->actingAs($this->recepcion)->put(route('citas.confirmar', $cita))->assertSessionHasErrors('estado');

        $this->assertSame(EstadoCita::Pendiente, $cita->fresh()?->estado);
    }

    public function test_CP_UT_05_rn02_confirma_con_pago_validado(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);
        Pago::factory()->confirmado()->create(['cita_id' => $cita->id]);

        $this->actingAs($this->recepcion)->put(route('citas.confirmar', $cita))->assertSessionHasNoErrors();

        $this->assertSame(EstadoCita::Confirmada, $cita->fresh()?->estado);
    }

    public function test_CP_UT_06_rn02_no_confirma_con_pago_rechazado(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);
        Pago::factory()->rechazado()->create(['cita_id' => $cita->id]);

        $this->actingAs($this->recepcion)->put(route('citas.confirmar', $cita))->assertSessionHasErrors('estado');

        $this->assertSame(EstadoCita::Pendiente, $cita->fresh()?->estado);
    }

    public function test_CP_UT_07_rn03_rechaza_una_hora_ocupada(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $this->citaPara($psicologo, atributos: ['hora' => '10:00']);

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), [
                'especialidad_id' => $especialidad->id, 'psicologo_id' => $psicologo->id,
                'fecha' => $this->proximoLunes(), 'hora' => '10:00',
            ])
            ->assertSessionHasErrors('hora');

        $this->assertDatabaseCount('citas', 1);
    }

    public function test_CP_UT_08_rn03_rechaza_una_hora_fuera_del_horario(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda(); // lunes 09:00-13:00

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), [
                'especialidad_id' => $especialidad->id, 'psicologo_id' => $psicologo->id,
                'fecha' => $this->proximoLunes(), 'hora' => '16:00',
            ])
            ->assertSessionHasErrors('hora');

        $this->assertDatabaseCount('citas', 0);
    }

    public function test_CP_UT_09_rechaza_una_fecha_pasada(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), [
                'especialidad_id' => $especialidad->id, 'psicologo_id' => $psicologo->id,
                'fecha' => now()->subDay()->toDateString(), 'hora' => '10:00',
            ])
            ->assertSessionHasErrors(['fecha' => 'La fecha de la cita no puede ser anterior a hoy.']);
    }
}
