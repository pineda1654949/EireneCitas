<?php

namespace Tests\Feature\Admin;

use App\Enums\Rol;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Promocion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Gestion de psicologos y promociones (solo administrador).
 */
class PsicologoYPromocionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->administrador()->create();
    }

    public function test_registra_un_psicologo_con_sus_especialidades(): void
    {
        $especialidades = Especialidad::factory()->count(2)->create();

        $this->actingAs($this->admin)
            ->post(route('admin.psicologos.store'), [
                'name' => 'Elena',
                'apellidos' => 'Rojas',
                'email' => 'elena@eirene.test',
                'password' => 'Psicologa2026',
                'activo' => '1',
                'especialidades' => $especialidades->pluck('id')->all(),
            ])
            ->assertRedirect(route('admin.psicologos.index'));

        $psicologo = User::where('email', 'elena@eirene.test')->sole();
        $this->assertSame(Rol::Psicologo, $psicologo->role);
        $this->assertTrue(Hash::check('Psicologa2026', $psicologo->password));
        $this->assertEqualsCanonicalizing($especialidades->pluck('id')->all(), $psicologo->especialidades->pluck('id')->all());
    }

    public function test_al_editar_la_contrasena_es_opcional(): void
    {
        $psicologo = User::factory()->psicologo()->create(['password' => 'Original2026']);

        $this->actingAs($this->admin)->get(route('admin.psicologos.edit', $psicologo))->assertOk();
        $this->actingAs($this->admin)
            ->put(route('admin.psicologos.update', $psicologo), [
                'name' => 'Nuevo nombre',
                'apellidos' => (string) $psicologo->apellidos,
                'email' => $psicologo->email,
                'password' => '',
                'activo' => '0',
            ])
            ->assertSessionHasNoErrors();

        $psicologo->refresh();
        $this->assertSame('Nuevo nombre', $psicologo->name);
        $this->assertFalse($psicologo->activo);
        $this->assertTrue(Hash::check('Original2026', $psicologo->password));
    }

    public function test_no_permite_editar_usuarios_que_no_son_psicologos(): void
    {
        $this->actingAs($this->admin)->get(route('admin.psicologos.edit', $this->admin))->assertNotFound();
        $this->actingAs($this->admin)->delete(route('admin.psicologos.destroy', $this->admin))->assertNotFound();

        $this->assertModelExists($this->admin);
    }

    public function test_un_psicologo_con_citas_se_desactiva_en_lugar_de_eliminarse(): void
    {
        $cita = Cita::factory()->create();
        $psicologo = $cita->psicologo;

        $this->actingAs($this->admin)->delete(route('admin.psicologos.destroy', $psicologo));

        $this->assertModelExists($psicologo);
        $this->assertFalse($psicologo->refresh()->activo);
    }

    public function test_un_psicologo_sin_citas_se_elimina(): void
    {
        $psicologo = User::factory()->psicologo()->create();

        $this->actingAs($this->admin)->delete(route('admin.psicologos.destroy', $psicologo));

        $this->assertModelMissing($psicologo);
    }

    public function test_crud_completo_de_promociones(): void
    {
        $this->actingAs($this->admin)->get(route('admin.promociones.create'))->assertOk();

        $this->actingAs($this->admin)
            ->post(route('admin.promociones.store'), ['nombre' => 'Pack verano', 'numero_sesiones' => 4, 'precio' => 250, 'activa' => '1'])
            ->assertRedirect(route('admin.promociones.index'));

        $promocion = Promocion::where('nombre', 'Pack verano')->sole();
        $this->assertTrue($promocion->activa);

        $this->actingAs($this->admin)->get(route('admin.promociones.edit', $promocion))->assertOk()->assertSee('Pack verano');
        $this->actingAs($this->admin)
            ->put(route('admin.promociones.update', $promocion), ['nombre' => 'Pack invierno', 'numero_sesiones' => 6, 'precio' => 300, 'activa' => '0'])
            ->assertSessionHasNoErrors();

        $promocion->refresh();
        $this->assertSame('Pack invierno', $promocion->nombre);
        $this->assertFalse($promocion->activa);

        $this->actingAs($this->admin)->delete(route('admin.promociones.destroy', $promocion));
        $this->assertModelMissing($promocion);
    }

    public function test_al_eliminar_una_promocion_las_citas_se_conservan(): void
    {
        $promocion = Promocion::factory()->create();
        $cita = Cita::factory()->create(['promocion_id' => $promocion->id]);

        $this->actingAs($this->admin)->delete(route('admin.promociones.destroy', $promocion));

        $this->assertNull($cita->refresh()->promocion_id);
    }

    public function test_valida_los_datos_de_la_promocion(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.promociones.store'), ['nombre' => '', 'numero_sesiones' => 0, 'precio' => -5])
            ->assertSessionHasErrors(['nombre', 'numero_sesiones', 'precio']);
    }
}
