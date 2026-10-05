<?php

namespace Tests\Feature\Psicologo;

use App\Models\Horario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * El psicologo administra su disponibilidad semanal (RF-02).
 */
class HorarioTest extends TestCase
{
    use RefreshDatabase;

    private User $psicologo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->psicologo = User::factory()->psicologo()->create();
    }

    public function test_agrega_un_bloque_de_atencion(): void
    {
        $this->actingAs($this->psicologo)
            ->post(route('psicologo.horarios.store'), ['dia_semana' => 2, 'hora_inicio' => '09:00', 'hora_fin' => '12:00'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('horarios', ['psicologo_id' => $this->psicologo->id, 'dia_semana' => 2, 'activo' => true]);
        $this->actingAs($this->psicologo)->get(route('psicologo.horarios.index'))->assertSee('Martes')->assertSee('09:00 – 12:00');
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function cruces(): array
    {
        // Bloque existente: 09:00-13:00
        return [
            'contenido dentro' => ['10:00', '11:00', true],
            'se cruza al inicio' => ['08:00', '09:30', true],
            'se cruza al final' => ['12:30', '14:00', true],
            'lo envuelve' => ['08:00', '14:00', true],
            'termina justo al empezar' => ['07:00', '09:00', false],
            'empieza justo al terminar' => ['13:00', '15:00', false],
        ];
    }

    #[DataProvider('cruces')]
    public function test_no_permite_bloques_que_se_crucen(string $inicio, string $fin, bool $seCruza): void
    {
        Horario::factory()->create(['psicologo_id' => $this->psicologo->id, 'dia_semana' => 1, 'hora_inicio' => '09:00', 'hora_fin' => '13:00']);

        $respuesta = $this->actingAs($this->psicologo)
            ->post(route('psicologo.horarios.store'), ['dia_semana' => 1, 'hora_inicio' => $inicio, 'hora_fin' => $fin]);

        $seCruza ? $respuesta->assertSessionHasErrors('hora_inicio') : $respuesta->assertSessionHasNoErrors();
    }

    public function test_el_fin_debe_ser_posterior_al_inicio(): void
    {
        $this->actingAs($this->psicologo)
            ->post(route('psicologo.horarios.store'), ['dia_semana' => 1, 'hora_inicio' => '12:00', 'hora_fin' => '10:00'])
            ->assertSessionHasErrors('hora_fin');
    }

    public function test_pausa_y_reactiva_un_bloque(): void
    {
        $bloque = Horario::factory()->create(['psicologo_id' => $this->psicologo->id]);

        $this->actingAs($this->psicologo)->patch(route('psicologo.horarios.alternar', $bloque));
        $this->assertFalse($bloque->refresh()->activo);

        $this->actingAs($this->psicologo)->patch(route('psicologo.horarios.alternar', $bloque));
        $this->assertTrue($bloque->refresh()->activo);
    }

    public function test_solo_modifica_sus_propios_bloques(): void
    {
        $ajeno = Horario::factory()->create();

        $this->actingAs($this->psicologo)->delete(route('psicologo.horarios.destroy', $ajeno))->assertForbidden();
        $this->actingAs($this->psicologo)->patch(route('psicologo.horarios.alternar', $ajeno))->assertForbidden();

        $this->assertModelExists($ajeno);
    }

    public function test_elimina_un_bloque_propio(): void
    {
        $bloque = Horario::factory()->create(['psicologo_id' => $this->psicologo->id]);

        $this->actingAs($this->psicologo)->delete(route('psicologo.horarios.destroy', $bloque));

        $this->assertModelMissing($bloque);
    }
}
