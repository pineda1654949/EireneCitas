<?php

namespace Tests\Feature\Api;

use App\Models\Cita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * Endpoints AJAX de disponibilidad en tiempo real (RF-02).
 */
class DisponibilidadApiTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    public function test_lista_los_psicologos_activos_de_una_especialidad(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $inactivo = User::factory()->psicologo()->inactivo()->create();
        $inactivo->especialidades()->attach($especialidad);

        $this->actingAs($this->usuarioPaciente())
            ->getJson(route('api.especialidades.psicologos', $especialidad))
            ->assertOk()
            ->assertExactJson([['id' => $psicologo->id, 'nombre' => $psicologo->nombre_completo]]);
    }

    public function test_devuelve_las_horas_libres(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $this->citaPara($psicologo, atributos: ['hora' => '10:00']);

        $this->actingAs($this->usuarioPaciente())
            ->getJson(route('api.horas-disponibles', ['psicologo_id' => $psicologo->id, 'fecha' => $this->proximoLunes()]))
            ->assertOk()
            ->assertExactJson(['09:00', '11:00', '12:00']);
    }

    public function test_solo_libera_la_hora_de_una_cita_que_el_usuario_puede_ver(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $propia = $this->citaPara($psicologo, $this->fichaDe($usuario), ['hora' => '09:00']);
        $ajena = $this->citaPara($psicologo, atributos: ['hora' => '10:00']);
        $consulta = fn (Cita $cita) => route('api.horas-disponibles', [
            'psicologo_id' => $psicologo->id, 'fecha' => $this->proximoLunes(), 'cita_id' => $cita->id,
        ]);

        $this->actingAs($usuario)->getJson($consulta($propia))->assertJsonFragment(['09:00'])->assertJsonMissing(['10:00']);
        $this->actingAs($usuario)->getJson($consulta($ajena))->assertJsonMissing(['10:00']);
    }

    public function test_valida_los_parametros(): void
    {
        $this->actingAs($this->usuarioPaciente())
            ->getJson(route('api.horas-disponibles', ['fecha' => 'manana']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['psicologo_id', 'fecha']);
    }

    public function test_requiere_sesion_iniciada(): void
    {
        $this->getJson(route('api.horas-disponibles'))->assertUnauthorized();
    }
}
