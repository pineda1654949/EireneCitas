<?php

namespace Tests\Feature\Admin;

use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Gestion de especialidades de la clinica (solo administrador).
 */
class EspecialidadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->administrador()->create();
    }

    public function test_lista_las_especialidades(): void
    {
        $especialidad = Especialidad::factory()->create(['nombre' => 'Terapia familiar']);

        $this->actingAs($this->admin)
            ->get(route('admin.especialidades.index'))
            ->assertOk()
            ->assertSee($especialidad->nombre);
    }

    public function test_registra_una_especialidad(): void
    {
        $this->actingAs($this->admin)->get(route('admin.especialidades.create'))->assertOk();

        $this->actingAs($this->admin)
            ->post(route('admin.especialidades.store'), [
                'nombre' => '  Terapia de duelo  ',
                'descripcion' => 'Acompañamiento ante pérdidas.',
            ])
            ->assertRedirect(route('admin.especialidades.index'));

        $this->assertDatabaseHas('especialidades', [
            'nombre' => 'Terapia de duelo',
            'descripcion' => 'Acompañamiento ante pérdidas.',
        ]);
    }

    public function test_el_nombre_es_obligatorio_y_unico(): void
    {
        Especialidad::factory()->create(['nombre' => 'Ansiedad']);

        $this->actingAs($this->admin)
            ->post(route('admin.especialidades.store'), ['nombre' => ''])
            ->assertSessionHasErrors('nombre');

        $this->actingAs($this->admin)
            ->post(route('admin.especialidades.store'), ['nombre' => 'Ansiedad'])
            ->assertSessionHasErrors('nombre');

        $this->assertSame(1, Especialidad::count());
    }

    public function test_edita_una_especialidad_y_puede_conservar_su_nombre(): void
    {
        $especialidad = Especialidad::factory()->create(['nombre' => 'Pareja']);

        $this->actingAs($this->admin)->get(route('admin.especialidades.edit', $especialidad))->assertOk();
        $this->actingAs($this->admin)
            ->put(route('admin.especialidades.update', $especialidad), [
                'nombre' => 'Pareja',
                'descripcion' => 'Terapia para parejas.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Terapia para parejas.', $especialidad->refresh()->descripcion);
    }

    public function test_elimina_una_especialidad_sin_uso(): void
    {
        $especialidad = Especialidad::factory()->create();

        $this->actingAs($this->admin)->delete(route('admin.especialidades.destroy', $especialidad));

        $this->assertModelMissing($especialidad);
    }

    public function test_no_elimina_una_especialidad_con_psicologos(): void
    {
        $especialidad = Especialidad::factory()->create();
        User::factory()->psicologo()->create()->especialidades()->attach($especialidad);

        $this->actingAs($this->admin)
            ->delete(route('admin.especialidades.destroy', $especialidad))
            ->assertSessionHasErrors('especialidad');

        $this->assertModelExists($especialidad);
    }

    public function test_no_elimina_una_especialidad_con_citas(): void
    {
        $especialidad = Especialidad::factory()->create();
        Cita::factory()->create(['especialidad_id' => $especialidad->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.especialidades.destroy', $especialidad))
            ->assertSessionHasErrors('especialidad');

        $this->assertModelExists($especialidad);
    }

    public function test_solo_el_administrador_gestiona_especialidades(): void
    {
        $this->actingAs(User::factory()->recepcionista()->create())
            ->get(route('admin.especialidades.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->paciente()->create())
            ->post(route('admin.especialidades.store'), ['nombre' => 'Intrusa'])
            ->assertForbidden();

        $this->assertSame(0, Especialidad::count());
    }
}
