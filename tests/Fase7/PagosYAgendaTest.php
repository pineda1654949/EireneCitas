<?php

namespace Tests\Fase7;

use App\Enums\EstadoCita;
use App\Enums\EstadoPago;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * Fase 7 - Integration Testing. Pagos RN-02 (CP-IT-12 a 16) y agenda y
 * reprogramacion RN-01 / RN-03 (CP-IT-17 a 22).
 */
class PagosYAgendaTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    private User $recepcion;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->recepcion = User::factory()->recepcionista()->create();
    }

    public function test_CP_IT_12_validar_pago_deja_la_cita_confirmada(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);
        $pago = Pago::factory()->create(['cita_id' => $cita->id]);

        $this->actingAs($this->recepcion)->put(route('pagos.validar', $pago))->assertRedirect();

        $this->assertSame(EstadoPago::Confirmado, $pago->fresh()?->estado);
        $this->assertSame(EstadoCita::Confirmada, $cita->fresh()?->estado);
    }

    public function test_CP_IT_13_rechazar_pago_deja_la_cita_sin_confirmar(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);
        $pago = Pago::factory()->create(['cita_id' => $cita->id]);

        $this->actingAs($this->recepcion)->put(route('pagos.rechazar', $pago))->assertRedirect();

        $this->assertSame(EstadoPago::Rechazado, $pago->fresh()?->estado);
        $this->assertSame(EstadoCita::Pendiente, $cita->fresh()?->estado);
    }

    public function test_CP_IT_14_pago_inexistente_devuelve_404(): void
    {
        $this->actingAs($this->recepcion)->put('/pagos/9999/validar')->assertNotFound();
    }

    public function test_CP_IT_15_psicologo_no_puede_validar_pagos(): void
    {
        $pago = Pago::factory()->create();

        $this->actingAs(User::factory()->psicologo()->create())->put(route('pagos.validar', $pago))->assertForbidden();

        $this->assertSame(EstadoPago::Pendiente, $pago->fresh()?->estado);
    }

    public function test_CP_IT_16_confirmar_sin_pago_validado_se_rechaza(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);

        $this->actingAs($this->recepcion)
            ->put(route('citas.confirmar', $cita))
            ->assertSessionHasErrors(['estado' => 'No se puede confirmar la cita: primero registra y valida su pago.']);

        $this->assertSame(EstadoCita::Pendiente, $cita->fresh()?->estado);
    }

    public function test_CP_IT_17_consultar_horas_disponibles(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $this->citaPara($psicologo, atributos: ['hora' => '11:00']);

        $this->actingAs($this->usuarioPaciente())
            ->getJson(route('api.horas-disponibles', ['psicologo_id' => $psicologo->id, 'fecha' => $this->proximoLunes()]))
            ->assertOk()
            ->assertExactJson(['09:00', '10:00', '12:00']);
    }

    public function test_CP_IT_18_reprogramar_valido_sube_el_contador_y_cambia_la_fecha(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['hora' => '10:00']);
        $nuevaFecha = now()->next('Monday')->addWeek()->toDateString();

        $this->actingAs($this->recepcion)
            ->put(route('citas.reprogramar', $cita), ['fecha' => $nuevaFecha, 'hora' => '11:00', 'motivo' => 'Viaje del paciente'])
            ->assertRedirect(route('citas.show', $cita));

        $cita->refresh();
        $this->assertSame(1, $cita->numero_reprogramaciones);
        $this->assertSame($nuevaFecha.' 11:00', $cita->fecha->toDateString().' '.$cita->hora_corta);
        $this->assertDatabaseHas('reprogramaciones', ['cita_id' => $cita->id, 'motivo' => 'Viaje del paciente']);
    }

    public function test_CP_IT_19_cuarto_cambio_se_rechaza_y_el_contador_sigue_en_3(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['numero_reprogramaciones' => 3]);

        $this->actingAs($this->recepcion)
            ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '12:00', 'motivo' => 'Otro cambio mas'])
            ->assertSessionHasErrors(['fecha' => 'Se alcanzó el límite de 3 reprogramaciones. Un nuevo cambio requiere la autorización del administrador.']);

        $this->assertSame(3, $cita->fresh()?->numero_reprogramaciones);
    }

    /**
     * RN-01: "Al 4.o intento el sistema rechaza y exige autorizacion del administrador".
     */
    public function test_CP_IT_19b_el_administrador_puede_autorizar_un_cambio_extra(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['numero_reprogramaciones' => 3, 'hora' => '10:00']);

        $this->actingAs(User::factory()->administrador()->create())
            ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '12:00', 'motivo' => 'Autorizado por direccion'])
            ->assertSessionHasNoErrors();

        $this->assertSame(4, $cita->fresh()?->numero_reprogramaciones);
    }

    public function test_CP_IT_20_reprogramar_a_una_hora_ocupada_se_rechaza(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $this->citaPara($psicologo, atributos: ['hora' => '12:00']);
        $cita = $this->citaPara($psicologo, atributos: ['hora' => '10:00']);

        $this->actingAs($this->recepcion)
            ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '12:00', 'motivo' => 'Cambio de horario'])
            ->assertSessionHasErrors('hora');

        $this->assertSame('10:00', $cita->fresh()?->hora_corta);
    }

    /**
     * RN-03. PHPUnit no ejecuta peticiones en paralelo: aqui se verifica la
     * logica (la segunda reserva ve la primera). La concurrencia real con
     * peticiones simultaneas se prueba con k6 (CP-SYS-06 / CP-SYS-25).
     */
    public function test_CP_IT_21_doble_reserva_solo_se_guarda_una(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $datos = [
            'especialidad_id' => $especialidad->id, 'psicologo_id' => $psicologo->id,
            'fecha' => $this->proximoLunes(), 'hora' => '10:00',
        ];

        $this->actingAs($this->usuarioPaciente())->post(route('citas.store'), $datos)->assertSessionHasNoErrors();
        $this->actingAs($this->usuarioPaciente())->post(route('citas.store'), $datos)->assertSessionHasErrors('hora');

        $this->assertSame(1, Cita::where('psicologo_id', $psicologo->id)->count());
    }

    public function test_CP_IT_22_psicologo_inexistente_da_error_y_no_500(): void
    {
        [, $especialidad] = $this->psicologoConAgenda();

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), [
                'especialidad_id' => $especialidad->id, 'psicologo_id' => 9999,
                'fecha' => $this->proximoLunes(), 'hora' => '10:00',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors(['psicologo_id' => 'El psicólogo seleccionado no es válido.']);
    }
}
