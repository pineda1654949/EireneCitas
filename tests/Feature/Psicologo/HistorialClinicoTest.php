<?php

namespace Tests\Feature\Psicologo;

use App\Enums\EstadoCita;
use App\Models\HistorialClinico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * RF-06: historia clinica (actividad TO-BE 15).
 */
class HistorialClinicoTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    public function test_el_psicologo_registra_la_sesion_y_la_cita_queda_atendida(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['estado' => EstadoCita::Confirmada, 'fecha' => now()->toDateString(), 'hora' => '09:00']);

        $this->actingAs($psicologo)->get(route('psicologo.historial.create', $cita))->assertOk();
        $this->actingAs($psicologo)
            ->post(route('psicologo.historial.store', $cita), ['notas_sesion' => 'Primera sesion, buena disposicion.', 'avance' => 'Inicial'])
            ->assertRedirect(route('citas.show', $cita));

        $historial = HistorialClinico::sole();
        $this->assertSame($cita->paciente_id, $historial->paciente_id);
        $this->assertSame('Primera sesion, buena disposicion.', $historial->notas_sesion);
        $this->assertSame(EstadoCita::Atendida, $cita->refresh()->estado);
    }

    public function test_registrar_dos_veces_actualiza_el_mismo_historial(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['fecha' => now()->toDateString(), 'hora' => '09:00']);

        $this->actingAs($psicologo)->post(route('psicologo.historial.store', $cita), ['notas_sesion' => 'Version 1']);
        $this->actingAs($psicologo)->post(route('psicologo.historial.store', $cita), ['notas_sesion' => 'Version 2']);

        $this->assertSame('Version 2', HistorialClinico::sole()->notas_sesion);
    }

    public function test_otro_psicologo_no_puede_atender_la_cita(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        [$otro] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);

        $this->actingAs($otro)->get(route('psicologo.historial.create', $cita))->assertForbidden();
        $this->actingAs($otro)->post(route('psicologo.historial.store', $cita), ['notas_sesion' => 'x'])->assertForbidden();

        $this->assertDatabaseCount('historiales_clinicos', 0);
    }

    public function test_no_se_atiende_una_cita_cancelada(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['estado' => EstadoCita::Cancelada]);

        $this->actingAs($psicologo)
            ->post(route('psicologo.historial.store', $cita), ['notas_sesion' => 'Notas'])
            ->assertSessionHasErrors('notas_sesion');

        $this->assertSame(EstadoCita::Cancelada, $cita->refresh()->estado);
    }

    public function test_las_notas_son_obligatorias(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);

        $this->actingAs($psicologo)
            ->post(route('psicologo.historial.store', $cita), ['notas_sesion' => ''])
            ->assertSessionHasErrors('notas_sesion');
    }

    public function test_el_psicologo_tratante_consulta_la_historia_clinica_completa(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['estado' => EstadoCita::Atendida]);
        HistorialClinico::factory()->create([
            'cita_id' => $cita->id,
            'paciente_id' => $cita->paciente_id,
            'psicologo_id' => $psicologo->id,
            'notas_sesion' => 'Evolucion favorable',
        ]);

        $this->actingAs($psicologo)
            ->get(route('psicologo.historial.paciente', $cita->paciente))
            ->assertOk()
            ->assertSee('Evolucion favorable');
    }

    public function test_un_psicologo_que_no_atiende_al_paciente_no_ve_su_historia(): void
    {
        $historial = HistorialClinico::factory()->create(['notas_sesion' => 'Dato sensible']);
        $ajeno = User::factory()->psicologo()->create();

        $this->actingAs($ajeno)
            ->get(route('psicologo.historial.paciente', $historial->paciente))
            ->assertForbidden();
    }

    public function test_el_personal_administrativo_no_ve_las_notas_clinicas(): void
    {
        $historial = HistorialClinico::factory()->create(['notas_sesion' => 'Dato sensible']);

        $this->actingAs(User::factory()->recepcionista()->create())
            ->get(route('citas.show', $historial->cita))
            ->assertOk()
            ->assertDontSee('Dato sensible');

        $this->actingAs(User::factory()->administrador()->create())
            ->get(route('psicologo.historial.paciente', $historial->paciente))
            ->assertForbidden();
    }
}
