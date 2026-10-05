<?php

namespace Tests\Feature\Pagos;

use App\Enums\EstadoCita;
use App\Enums\EstadoPago;
use App\Enums\MetodoPago;
use App\Models\Pago;
use App\Models\User;
use App\Notifications\CitaConfirmada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * Control de pagos y confirmacion automatica de la cita (RF-04).
 */
class PagoTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    private User $recepcion;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->recepcion = User::factory()->recepcionista()->create();
    }

    public function test_registra_un_pago_pendiente(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);

        $this->actingAs($this->recepcion)->get(route('pagos.create', $cita))->assertOk();
        $this->actingAs($this->recepcion)
            ->post(route('pagos.store', $cita), ['monto' => '80.00', 'metodo_pago' => 'yape_plin', 'numero_comprobante' => 'OP-123'])
            ->assertRedirect(route('citas.show', $cita));

        $pago = Pago::sole();
        $this->assertSame(EstadoPago::Pendiente, $pago->estado);
        $this->assertSame(MetodoPago::YapePlin, $pago->metodo_pago);
        $this->assertSame('80.00', $pago->monto);
        $this->assertSame(EstadoCita::Pendiente, $cita->refresh()->estado);
    }

    public function test_valida_monto_y_metodo(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);

        $this->actingAs($this->recepcion)
            ->post(route('pagos.store', $cita), ['monto' => '0', 'metodo_pago' => 'bitcoin'])
            ->assertSessionHasErrors(['monto', 'metodo_pago']);

        $this->assertDatabaseCount('pagos', 0);
    }

    public function test_no_registra_pagos_de_una_cita_cancelada(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['estado' => EstadoCita::Cancelada]);

        $this->actingAs($this->recepcion)
            ->post(route('pagos.store', $cita), ['monto' => '80', 'metodo_pago' => 'efectivo'])
            ->assertSessionHasErrors('monto');

        $this->assertDatabaseCount('pagos', 0);
    }

    public function test_validar_el_pago_confirma_la_cita(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);
        $pago = Pago::factory()->create(['cita_id' => $cita->id]);

        $this->actingAs($this->recepcion)
            ->put(route('pagos.validar', $pago))
            ->assertSessionHas('status', 'Pago validado y cita confirmada.');

        $pago->refresh();
        $this->assertSame(EstadoPago::Confirmado, $pago->estado);
        $this->assertSame($this->recepcion->id, $pago->validado_por);
        $this->assertNotNull($pago->fecha_pago);
        $this->assertSame(EstadoCita::Confirmada, $cita->refresh()->estado);
        Notification::assertSentTo($cita->paciente, CitaConfirmada::class);
    }

    public function test_validar_el_pago_de_una_cita_cancelada_no_la_reactiva(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['estado' => EstadoCita::Cancelada]);
        $pago = Pago::factory()->create(['cita_id' => $cita->id]);

        $this->actingAs($this->recepcion)->put(route('pagos.validar', $pago));

        $this->assertSame(EstadoPago::Confirmado, $pago->refresh()->estado);
        $this->assertSame(EstadoCita::Cancelada, $cita->refresh()->estado);
        Notification::assertNothingSent();
    }

    public function test_un_pago_ya_procesado_no_se_vuelve_a_procesar(): void
    {
        $pago = Pago::factory()->rechazado()->create();

        $this->actingAs($this->recepcion)->put(route('pagos.validar', $pago))->assertSessionHasErrors('pago');
        $this->actingAs($this->recepcion)->put(route('pagos.rechazar', $pago))->assertSessionHasErrors('pago');

        $this->assertSame(EstadoPago::Rechazado, $pago->refresh()->estado);
    }

    public function test_rechazar_un_pago_no_confirma_la_cita(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);
        $pago = Pago::factory()->create(['cita_id' => $cita->id]);

        $this->actingAs($this->recepcion)->put(route('pagos.rechazar', $pago));

        $this->assertSame(EstadoPago::Rechazado, $pago->refresh()->estado);
        $this->assertSame(EstadoCita::Pendiente, $cita->refresh()->estado);
    }

    public function test_lista_y_filtra_pagos_por_estado(): void
    {
        $pendiente = Pago::factory()->create(['numero_comprobante' => 'OP-PENDIENTE']);
        Pago::factory()->confirmado()->create(['numero_comprobante' => 'OP-CONFIRMADO']);

        $this->actingAs($this->recepcion)
            ->get(route('pagos.index', ['estado' => 'pendiente']))
            ->assertOk()
            ->assertSee('OP-PENDIENTE')
            ->assertDontSee('OP-CONFIRMADO');

        $this->assertNotNull($pendiente);
    }

    public function test_solo_el_personal_valida_pagos_y_el_psicologo_no_los_registra(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $cita = $this->citaPara($psicologo, $this->fichaDe($usuario));
        $pago = Pago::factory()->create(['cita_id' => $cita->id]);

        // El psicologo no registra pagos; el paciente si puede reportarlos (con voucher).
        $this->actingAs($psicologo)->post(route('pagos.store', $cita), ['monto' => 80, 'metodo_pago' => 'efectivo'])->assertForbidden();

        foreach ([$usuario, $psicologo] as $actor) {
            $this->actingAs($actor)->put(route('pagos.validar', $pago))->assertForbidden();
            $this->actingAs($actor)->put(route('pagos.rechazar', $pago))->assertForbidden();
        }

        $this->assertSame(EstadoPago::Pendiente, $pago->refresh()->estado);
    }
}
