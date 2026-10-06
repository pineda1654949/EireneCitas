<?php

namespace Tests\Fase7;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Promocion;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * Fase 7 - Integration Testing. Autenticacion (CP-IT-01 a 04) y pacientes y
 * citas (CP-IT-05 a 11). Proyecto EireneCitas sobre Laravel 12.
 */
class AutenticacionYPacientesTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_CP_IT_01_login_valido_inicia_sesion_y_redirige_a_home(): void
    {
        $usuario = User::factory()->create(['email' => 'ana@eirene.test']);

        $this->post(route('login'), ['email' => 'ana@eirene.test', 'password' => UserFactory::CONTRASENA])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_CP_IT_02_clave_incorrecta_vuelve_con_error(): void
    {
        User::factory()->create(['email' => 'ana@eirene.test']);

        $this->from(route('login'))
            ->post(route('login'), ['email' => 'ana@eirene.test', 'password' => 'Incorrecta1'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Las credenciales no coinciden con nuestros registros.']);

        $this->assertGuest();
    }

    public function test_CP_IT_03_usuario_inexistente_recibe_el_mismo_mensaje(): void
    {
        $this->post(route('login'), ['email' => 'nadie@eirene.test', 'password' => 'Incorrecta1'])
            ->assertSessionHasErrors(['email' => 'Las credenciales no coinciden con nuestros registros.']);

        $this->assertGuest();
    }

    public function test_CP_IT_04_usuario_inactivo_no_puede_ingresar(): void
    {
        User::factory()->inactivo()->create(['email' => 'ana@eirene.test']);

        $this->post(route('login'), ['email' => 'ana@eirene.test', 'password' => UserFactory::CONTRASENA])
            ->assertSessionHasErrors(['email' => 'Tu cuenta se encuentra inactiva. Contacta al administrador.']);

        $this->assertGuest();
    }

    public function test_CP_IT_05_crear_paciente_valido(): void
    {
        $this->actingAs(User::factory()->recepcionista()->create())
            ->post(route('admin.pacientes.store'), [
                'nombres' => 'Lucia', 'apellidos' => 'Quispe', 'dni' => '71234567',
                'edad' => 30, 'correo' => 'lucia@correo.pe', 'telefono' => '987654321',
            ])
            ->assertRedirect(route('admin.pacientes.index'));

        $this->assertDatabaseHas('pacientes', ['dni' => '71234567', 'estado_atencion' => 'inscripto']);
    }

    /**
     * Si falla la creacion de la ficha despues de crear la cuenta, la
     * transaccion revierte todo: no queda un usuario sin ficha.
     */
    public function test_CP_IT_06_un_fallo_a_mitad_del_proceso_revierte_todo(): void
    {
        Paciente::creating(fn () => throw new RuntimeException('Fallo simulado al guardar la ficha'));

        $this->post(route('register'), [
            'name' => 'Lucia', 'apellidos' => 'Quispe', 'email' => 'lucia@correo.pe',
            'password' => 'Segura2026', 'password_confirmation' => 'Segura2026',
        ])->assertStatus(500);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('pacientes', 0);
        $this->assertGuest();
    }

    public function test_CP_IT_07_dni_duplicado_da_error_y_deja_un_solo_registro(): void
    {
        Paciente::factory()->create(['dni' => '71234567']);

        $this->actingAs(User::factory()->recepcionista()->create())
            ->post(route('admin.pacientes.store'), ['nombres' => 'Otra', 'apellidos' => 'Persona', 'dni' => '71234567'])
            ->assertSessionHasErrors(['dni' => 'El DNI ya está registrado.']);

        $this->assertSame(1, Paciente::where('dni', '71234567')->count());
    }

    public function test_CP_IT_08_datos_invalidos_devuelven_errores_por_campo(): void
    {
        $this->actingAs(User::factory()->recepcionista()->create())
            ->post(route('admin.pacientes.store'), [
                'nombres' => '', 'apellidos' => '', 'dni' => 'abc', 'edad' => 0,
                'correo' => 'no-es-correo', 'telefono' => '123',
            ])
            ->assertSessionHasErrors(['nombres', 'apellidos', 'dni', 'edad', 'correo', 'telefono']);

        $this->assertDatabaseCount('pacientes', 0);
    }

    public function test_CP_IT_09_cuotas_segun_la_promocion_se_guardan(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $promocion = Promocion::factory()->enCuotas(3)->create(['precio' => 300]);
        $cita = $this->citaPara($psicologo, atributos: ['promocion_id' => $promocion->id]);
        $recepcion = User::factory()->recepcionista()->create();

        foreach ([1, 2] as $cuota) {
            $this->actingAs($recepcion)
                ->post(route('pagos.store', $cita), ['monto' => 100, 'metodo_pago' => 'yape_plin', 'total_cuotas' => 3])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame([[1, 3], [2, 3]], $cita->pagos()->orderBy('id')->get()->map(fn ($p) => [$p->numero_cuota, $p->total_cuotas])->all());
    }

    public function test_CP_IT_10_cuotas_sobre_el_maximo_se_rechazan(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $promocion = Promocion::factory()->enCuotas(3)->create();
        $cita = $this->citaPara($psicologo, atributos: ['promocion_id' => $promocion->id]);
        $sinCuotas = $this->citaPara($psicologo, atributos: ['hora' => '11:00']);
        $recepcion = User::factory()->recepcionista()->create();

        $this->actingAs($recepcion)
            ->post(route('pagos.store', $cita), ['monto' => 50, 'metodo_pago' => 'yape_plin', 'total_cuotas' => 4])
            ->assertSessionHasErrors(['total_cuotas' => 'La promoción permite como máximo 3 cuotas.']);

        $this->actingAs($recepcion)
            ->post(route('pagos.store', $sinCuotas), ['monto' => 50, 'metodo_pago' => 'yape_plin', 'total_cuotas' => 2])
            ->assertSessionHasErrors('total_cuotas');

        $this->assertDatabaseCount('pagos', 0);
    }

    public function test_CP_IT_11_promocion_inactiva_se_rechaza(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $promocion = Promocion::factory()->inactiva()->create();

        $this->actingAs($this->usuarioPaciente())
            ->post(route('citas.store'), [
                'especialidad_id' => $especialidad->id, 'psicologo_id' => $psicologo->id,
                'fecha' => $this->proximoLunes(), 'hora' => '10:00', 'promocion_id' => $promocion->id,
            ])
            ->assertSessionHasErrors('promocion_id');

        $this->assertSame(0, Cita::count());
    }
}
