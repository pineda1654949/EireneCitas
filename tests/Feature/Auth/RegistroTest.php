<?php

namespace Tests\Feature\Auth;

use App\Enums\Rol;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * RF-05: registro publico de pacientes.
 */
class RegistroTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function datos(array $cambios = []): array
    {
        return [
            'name' => 'Lucia',
            'apellidos' => 'Quispe Mamani',
            'dni' => '71234567',
            'email' => 'lucia@correo.pe',
            'telefono' => '987654321',
            'password' => 'Segura2026',
            'password_confirmation' => 'Segura2026',
            ...$cambios,
        ];
    }

    public function test_muestra_el_formulario_de_registro(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Crea tu cuenta de paciente');
    }

    public function test_crea_la_cuenta_de_paciente_y_su_ficha(): void
    {
        $this->post(route('register'), $this->datos())->assertRedirect(route('home'));

        $usuario = User::where('email', 'lucia@correo.pe')->sole();
        $this->assertSame(Rol::Paciente, $usuario->role);
        $this->assertAuthenticatedAs($usuario);

        $ficha = Paciente::where('user_id', $usuario->id)->sole();
        $this->assertSame('Lucia', $ficha->nombres);
        $this->assertSame('71234567', $ficha->dni);
        $this->assertSame('lucia@correo.pe', $ficha->correo);
    }

    public function test_nadie_puede_registrarse_como_administrador(): void
    {
        $this->post(route('register'), $this->datos(['role' => 'administrador']));

        $this->assertSame(Rol::Paciente, User::where('email', 'lucia@correo.pe')->sole()->role);
    }

    /**
     * @return array<string, array{array<string, string>, string}>
     */
    public static function datosInvalidos(): array
    {
        return [
            'contrasena corta' => [['password' => 'Ab1', 'password_confirmation' => 'Ab1'], 'password'],
            'contrasena sin numeros' => [['password' => 'SoloLetras', 'password_confirmation' => 'SoloLetras'], 'password'],
            'contrasena sin letras' => [['password' => '12345678', 'password_confirmation' => '12345678'], 'password'],
            'confirmacion distinta' => [['password_confirmation' => 'Otra2026'], 'password'],
            'correo invalido' => [['email' => 'no-es-correo'], 'email'],
            'dni con letras' => [['dni' => '7123A567'], 'dni'],
            'sin nombres' => [['name' => ''], 'name'],
        ];
    }

    /**
     * @param  array<string, string>  $cambios
     */
    #[DataProvider('datosInvalidos')]
    public function test_valida_los_datos_de_registro(array $cambios, string $campo): void
    {
        $this->post(route('register'), $this->datos($cambios))->assertSessionHasErrors($campo);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_no_permite_correos_ni_dni_duplicados(): void
    {
        User::factory()->create(['email' => 'lucia@correo.pe', 'dni' => '71234567']);

        $this->post(route('register'), $this->datos())->assertSessionHasErrors(['email', 'dni']);
    }

    public function test_los_mensajes_de_error_estan_en_espanol(): void
    {
        $this->post(route('register'), $this->datos(['name' => '']))
            ->assertSessionHasErrors(['name' => 'El campo nombres es obligatorio.']);
    }
}
