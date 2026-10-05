<?php

namespace Tests\Feature\Citas;

use App\Enums\EstadoCita;
use App\Models\Reprogramacion;
use App\Models\User;
use App\Notifications\CitaReprogramada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * RF-03: reprogramacion de citas (maximo 3 veces).
 */
class ReprogramarCitaTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_el_paciente_reprograma_su_cita_a_una_hora_libre(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $cita = $this->citaPara($psicologo, $this->fichaDe($usuario), ['hora' => '10:00']);

        $this->actingAs($usuario)
            ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '12:00', 'motivo' => 'Viaje de trabajo'])
            ->assertRedirect(route('citas.show', $cita))
            ->assertSessionHasNoErrors();

        $cita->refresh();
        $this->assertSame('12:00', $cita->hora_corta);
        $this->assertSame(EstadoCita::Reprogramada, $cita->estado);
        $this->assertSame(1, $cita->numero_reprogramaciones);

        $cambio = Reprogramacion::sole();
        $this->assertSame(Reprogramacion::TIPO_REPROGRAMACION, $cambio->tipo);
        $this->assertSame('Viaje de trabajo', $cambio->motivo);
        $this->assertSame($usuario->id, $cambio->realizado_por);
        $this->assertSame('10:00', substr((string) $cambio->hora_anterior, 0, 5));

        Notification::assertSentTo($psicologo, CitaReprogramada::class);
    }

    public function test_puede_mantener_su_misma_hora_en_otra_fecha(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['hora' => '10:00']);
        $lunesSiguiente = now()->next('Monday')->addWeek()->toDateString();

        $this->actingAs(User::factory()->recepcionista()->create())
            ->put(route('citas.reprogramar', $cita), ['fecha' => $lunesSiguiente, 'hora' => '10:00', 'motivo' => 'Cambio solicitado'])
            ->assertSessionHasNoErrors();

        $this->assertSame($lunesSiguiente, $cita->refresh()->fecha->toDateString());
    }

    public function test_no_permite_mover_la_cita_a_una_hora_ocupada(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $this->citaPara($psicologo, atributos: ['hora' => '11:00']);
        $cita = $this->citaPara($psicologo, atributos: ['hora' => '10:00']);

        $this->actingAs(User::factory()->recepcionista()->create())
            ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '11:00', 'motivo' => 'Cambio solicitado'])
            ->assertSessionHasErrors('hora');

        $this->assertSame('10:00', $cita->refresh()->hora_corta);
        $this->assertSame(0, $cita->numero_reprogramaciones);
    }

    public function test_no_permite_una_hora_fuera_del_horario(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);

        $this->actingAs(User::factory()->recepcionista()->create())
            ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '03:00', 'motivo' => 'Cambio solicitado'])
            ->assertSessionHasErrors('hora');
    }

    public function test_respeta_el_limite_de_tres_reprogramaciones(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['numero_reprogramaciones' => 3]);
        $recepcion = User::factory()->recepcionista()->create();

        $this->actingAs($recepcion)
            ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '12:00', 'motivo' => 'Cambio solicitado'])
            ->assertSessionHasErrors('fecha');

        $this->actingAs($recepcion)
            ->get(route('citas.reprogramar.form', $cita))
            ->assertRedirect(route('citas.show', $cita));

        $this->assertSame(3, $cita->refresh()->numero_reprogramaciones);
    }

    public function test_tres_reprogramaciones_seguidas_agotan_el_limite(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['hora' => '09:00']);
        $this->actingAs(User::factory()->recepcionista()->create());

        foreach (['10:00', '11:00', '12:00'] as $hora) {
            $this->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => $hora, 'motivo' => 'Cambio solicitado'])->assertSessionHasNoErrors();
        }

        $this->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '09:00', 'motivo' => 'Cambio solicitado'])->assertSessionHasErrors('fecha');

        $this->assertSame(3, $cita->refresh()->numero_reprogramaciones);
        $this->assertSame(3, Reprogramacion::count());
    }

    public function test_una_cita_cancelada_no_se_reprograma(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['estado' => EstadoCita::Cancelada]);

        $this->actingAs(User::factory()->recepcionista()->create())
            ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '12:00', 'motivo' => 'Cambio solicitado'])
            ->assertSessionHasErrors('fecha');
    }

    public function test_un_paciente_no_reprograma_citas_ajenas(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);

        $this->actingAs($this->usuarioPaciente())
            ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '12:00', 'motivo' => 'Cambio solicitado'])
            ->assertForbidden();
    }

    public function test_el_formulario_muestra_las_reprogramaciones_restantes(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $cita = $this->citaPara($psicologo, $this->fichaDe($usuario), ['numero_reprogramaciones' => 1]);

        $this->actingAs($usuario)
            ->get(route('citas.reprogramar.form', $cita))
            ->assertOk()
            ->assertSee('2 de 3');
    }
}
