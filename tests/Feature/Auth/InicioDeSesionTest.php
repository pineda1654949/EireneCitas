<?php

namespace Tests\Feature\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RF-07: autenticacion de usuarios.
 */
class InicioDeSesionTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_el_formulario_de_inicio_de_sesion(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Inicia sesión');
    }

    public function test_inicia_sesion_con_correo_y_contrasena(): void
    {
        $usuario = User::factory()->create(['email' => 'ana@eirene.test']);

        $this->post(route('login'), ['email' => 'ana@eirene.test', 'password' => UserFactory::CONTRASENA])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_el_administrador_inicia_sesion_con_nombre_de_usuario(): void
    {
        $admin = User::factory()->administrador()->create(['email' => 'admin', 'password' => 'contraseña']);

        $this->post(route('login'), ['email' => 'admin', 'password' => 'contraseña'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_rechaza_una_contrasena_incorrecta(): void
    {
        User::factory()->create(['email' => 'ana@eirene.test']);

        $this->from(route('login'))
            ->post(route('login'), ['email' => 'ana@eirene.test', 'password' => 'incorrecta'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Las credenciales no coinciden con nuestros registros.']);

        $this->assertGuest();
    }

    public function test_requiere_usuario_y_contrasena(): void
    {
        $this->post(route('login'), [])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_bloquea_temporalmente_tras_varios_intentos_fallidos(): void
    {
        User::factory()->create(['email' => 'ana@eirene.test']);

        for ($i = 0; $i < LoginRequest::MAX_INTENTOS; $i++) {
            $this->post(route('login'), ['email' => 'ana@eirene.test', 'password' => 'incorrecta']);
        }

        // Incluso con la contrasena correcta, el usuario queda bloqueado un tiempo.
        $respuesta = $this->post(route('login'), ['email' => 'ana@eirene.test', 'password' => UserFactory::CONTRASENA]);

        $respuesta->assertSessionHasErrors('email');
        $this->assertStringContainsString('Demasiados intentos', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_una_cuenta_inactiva_no_puede_ingresar(): void
    {
        User::factory()->inactivo()->create(['email' => 'ana@eirene.test']);

        $this->post(route('login'), ['email' => 'ana@eirene.test', 'password' => UserFactory::CONTRASENA])
            ->assertSessionHasErrors(['email' => 'Tu cuenta se encuentra inactiva. Contacta al administrador.']);

        $this->assertGuest();
    }

    public function test_una_sesion_abierta_se_cierra_si_la_cuenta_se_desactiva(): void
    {
        $usuario = User::factory()->administrador()->create();
        $this->actingAs($usuario)->get(route('home'))->assertOk();

        $usuario->update(['activo' => false]);

        $this->get(route('home'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_cierra_sesion(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_regenera_la_sesion_al_iniciar_sesion(): void
    {
        User::factory()->create(['email' => 'ana@eirene.test']);
        $this->startSession();
        $idAnterior = session()->getId();

        $this->post(route('login'), ['email' => 'ana@eirene.test', 'password' => UserFactory::CONTRASENA]);

        $this->assertNotSame($idAnterior, session()->getId());
    }
}
