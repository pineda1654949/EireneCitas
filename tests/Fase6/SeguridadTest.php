<?php

namespace Tests\Fase6;

use App\Http\Middleware\VerificarRol;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Fase 6 - Unit Testing. Seguridad (CP-UT-31 a CP-UT-38).
 */
class SeguridadTest extends TestCase
{
    use RefreshDatabase;

    public function test_CP_UT_31_middleware_rol_bloquea_un_rol_no_permitido(): void
    {
        $peticion = Request::create('/admin/promociones');
        $peticion->setUserResolver(fn () => User::factory()->psicologo()->make());

        try {
            (new VerificarRol)->handle($peticion, fn () => response('ok'), 'administrador');
            $this->fail('El middleware debio bloquear la peticion.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_CP_UT_31_middleware_rol_deja_pasar_un_rol_permitido(): void
    {
        $peticion = Request::create('/admin/promociones');
        $peticion->setUserResolver(fn () => User::factory()->administrador()->make());

        $respuesta = (new VerificarRol)->handle($peticion, fn () => response('ok'), 'administrador', 'recepcionista');

        $this->assertSame('ok', $respuesta->getContent());
    }

    public function test_CP_UT_32_middleware_auth_sin_sesion_redirige_al_login(): void
    {
        $this->get(route('citas.index'))->assertRedirect(route('login'));
    }

    public function test_CP_UT_33_usuario_de_otro_rol_que_cambia_de_ruta_es_bloqueado(): void
    {
        $psicologo = User::factory()->psicologo()->create();

        $this->actingAs($psicologo)->get(route('psicologo.horarios.index'))->assertOk();
        $this->actingAs($psicologo)->get(route('admin.pacientes.index'))->assertForbidden();
        $this->actingAs($psicologo)->get(route('admin.promociones.index'))->assertForbidden();
    }

    /**
     * Simula la expiracion: los datos de la sesion desaparecen. La expiracion
     * real por tiempo (SESSION_LIFETIME) se verifica en navegador (CP-SYS-12).
     */
    public function test_CP_UT_34_sesion_expirada_redirige_al_login(): void
    {
        $usuario = User::factory()->create(['email' => 'ana@eirene.test']);
        $this->post(route('login'), ['email' => 'ana@eirene.test', 'password' => UserFactory::CONTRASENA]);
        $this->assertAuthenticatedAs($usuario);

        $this->flushSession();
        $this->app['auth']->forgetGuards();

        $this->get(route('home'))->assertRedirect(route('login'));
        $this->assertSame(120, (int) config('session.lifetime'));
    }

    public function test_CP_UT_35_login_valido_inicia_y_regenera_la_sesion(): void
    {
        $usuario = User::factory()->create(['email' => 'ana@eirene.test']);
        $this->startSession();
        $idAnterior = session()->getId();

        $this->post(route('login'), ['email' => 'ana@eirene.test', 'password' => UserFactory::CONTRASENA])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($usuario);
        $this->assertNotSame($idAnterior, session()->getId());
    }

    public function test_CP_UT_36_hash_make_genera_un_hash_distinto_verificable(): void
    {
        $hash = Hash::make('Segura2026');

        $this->assertNotSame('Segura2026', $hash);
        $this->assertStringStartsWith('$2y$', $hash); // bcrypt
        $this->assertTrue(Hash::check('Segura2026', $hash));
    }

    public function test_CP_UT_37_contrasena_incorrecta_no_verifica(): void
    {
        $this->assertFalse(Hash::check('otraClave1', Hash::make('Segura2026')));
    }

    public function test_CP_UT_38_excepcion_con_debug_desactivado_no_muestra_detalles(): void
    {
        config(['app.debug' => false]);
        Route::middleware('web')->get('/_forzar-error', fn () => throw new \RuntimeException('Detalle interno secreto en ArchivoSecreto.php'));

        $respuesta = $this->get('/_forzar-error');

        $respuesta->assertStatus(500)
            ->assertSee('Algo salió mal')
            ->assertDontSee('Detalle interno secreto')
            ->assertDontSee('ArchivoSecreto.php')
            ->assertDontSee('Stack trace');
    }
}
