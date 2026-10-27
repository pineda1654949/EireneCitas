<?php

namespace Tests\Feature\Admin;

use App\Enums\Rol;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Gestion de cuentas de recepcionista (solo administrador).
 */
class RecepcionistaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->administrador()->create();
    }

    public function test_lista_solo_a_las_recepcionistas(): void
    {
        $recepcionista = User::factory()->recepcionista()->create();
        $psicologo = User::factory()->psicologo()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.recepcionistas.index'))
            ->assertOk()
            ->assertSee($recepcionista->email)
            ->assertDontSee($psicologo->email);
    }

    public function test_registra_una_recepcionista(): void
    {
        $this->actingAs($this->admin)->get(route('admin.recepcionistas.create'))->assertOk();

        $this->actingAs($this->admin)
            ->post(route('admin.recepcionistas.store'), [
                'name' => 'Rosa',
                'apellidos' => 'Quispe',
                'email' => 'ROSA@eirene.test',
                'password' => 'Recepcion2026',
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.recepcionistas.index'));

        $recepcionista = User::where('email', 'rosa@eirene.test')->sole();
        $this->assertSame(Rol::Recepcionista, $recepcionista->role);
        $this->assertTrue($recepcionista->activo);
        $this->assertTrue(Hash::check('Recepcion2026', $recepcionista->password));
    }

    public function test_valida_los_datos_obligatorios_y_el_correo_unico(): void
    {
        $existente = User::factory()->recepcionista()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.recepcionistas.store'), [
                'name' => '',
                'apellidos' => '',
                'email' => $existente->email,
                'password' => '',
            ])
            ->assertSessionHasErrors(['name', 'apellidos', 'email', 'password']);

        $this->assertSame(1, User::recepcionistas()->count());
    }

    public function test_al_editar_la_contrasena_es_opcional(): void
    {
        $recepcionista = User::factory()->recepcionista()->create(['password' => 'Original2026']);

        $this->actingAs($this->admin)->get(route('admin.recepcionistas.edit', $recepcionista))->assertOk();
        $this->actingAs($this->admin)
            ->put(route('admin.recepcionistas.update', $recepcionista), [
                'name' => 'Nuevo nombre',
                'apellidos' => (string) $recepcionista->apellidos,
                'email' => $recepcionista->email,
                'password' => '',
                'activo' => '0',
            ])
            ->assertSessionHasNoErrors();

        $recepcionista->refresh();
        $this->assertSame('Nuevo nombre', $recepcionista->name);
        $this->assertFalse($recepcionista->activo);
        $this->assertTrue(Hash::check('Original2026', $recepcionista->password));
    }

    public function test_no_permite_editar_usuarios_que_no_son_recepcionistas(): void
    {
        $this->actingAs($this->admin)->get(route('admin.recepcionistas.edit', $this->admin))->assertNotFound();
        $this->actingAs($this->admin)->delete(route('admin.recepcionistas.destroy', $this->admin))->assertNotFound();

        $this->assertModelExists($this->admin);
    }

    public function test_elimina_una_recepcionista_sin_pagos(): void
    {
        $recepcionista = User::factory()->recepcionista()->create();

        $this->actingAs($this->admin)->delete(route('admin.recepcionistas.destroy', $recepcionista));

        $this->assertModelMissing($recepcionista);
    }

    public function test_una_recepcionista_con_pagos_se_desactiva_en_lugar_de_eliminarse(): void
    {
        $recepcionista = User::factory()->recepcionista()->create();
        Pago::factory()->create(['validado_por' => $recepcionista->id]);

        $this->actingAs($this->admin)->delete(route('admin.recepcionistas.destroy', $recepcionista));

        $this->assertModelExists($recepcionista);
        $this->assertFalse($recepcionista->refresh()->activo);
    }

    public function test_solo_el_administrador_gestiona_recepcionistas(): void
    {
        $recepcionista = User::factory()->recepcionista()->create();

        $this->actingAs($recepcionista)->get(route('admin.recepcionistas.index'))->assertForbidden();
        $this->actingAs(User::factory()->psicologo()->create())->get(route('admin.recepcionistas.create'))->assertForbidden();
    }
}
