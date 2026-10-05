<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\RestablecerContrasena;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class RecuperarContrasenaTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_el_formulario_de_recuperacion(): void
    {
        $this->get(route('password.request'))->assertOk()->assertSee('¿Olvidaste tu contraseña?');
    }

    public function test_envia_el_enlace_al_correo_registrado(): void
    {
        Notification::fake();
        $usuario = User::factory()->create(['email' => 'ana@eirene.test']);

        $this->post(route('password.email'), ['email' => 'ana@eirene.test'])->assertSessionHas('status');

        Notification::assertSentTo($usuario, RestablecerContrasena::class, function (RestablecerContrasena $notificacion) use ($usuario) {
            $correo = $notificacion->toMail($usuario);

            return str_contains($correo->subject, 'Restablecer contrasena')
                && str_contains((string) $correo->actionUrl, '/restablecer-contrasena/');
        });
    }

    public function test_no_revela_si_el_correo_existe(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'nadie@eirene.test'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    public function test_restablece_la_contrasena_con_un_token_valido(): void
    {
        $usuario = User::factory()->create(['email' => 'ana@eirene.test']);
        $token = Password::createToken($usuario);

        $this->get(route('password.reset', $token))->assertOk();

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => 'ana@eirene.test',
            'password' => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NuevaClave2026', (string) $usuario->fresh()?->password));
    }

    public function test_rechaza_un_token_invalido(): void
    {
        $usuario = User::factory()->create(['email' => 'ana@eirene.test']);

        $this->post(route('password.store'), [
            'token' => 'token-falso',
            'email' => 'ana@eirene.test',
            'password' => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ])->assertSessionHasErrors('email');

        $this->assertFalse(Hash::check('NuevaClave2026', (string) $usuario->fresh()?->password));
    }

    public function test_la_nueva_contrasena_cumple_la_politica(): void
    {
        $usuario = User::factory()->create(['email' => 'ana@eirene.test']);

        $this->post(route('password.store'), [
            'token' => Password::createToken($usuario),
            'email' => 'ana@eirene.test',
            'password' => 'corta',
            'password_confirmation' => 'corta',
        ])->assertSessionHasErrors('password');
    }
}
