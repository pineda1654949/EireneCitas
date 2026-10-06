<?php

namespace Tests\Feature\Seguridad;

use App\Enums\Rol;
use App\Models\Cita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * RF-07: control de acceso por rol. Matriz de permisos de cada modulo.
 */
class ControlDeAccesoTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    private function usuarioCon(Rol $rol): User
    {
        return match ($rol) {
            Rol::Paciente => $this->usuarioPaciente(),
            default => User::factory()->create(['role' => $rol]),
        };
    }

    /**
     * Ruta => roles que pueden entrar. El resto debe recibir 403.
     *
     * @return array<string, array{string, list<Rol>}>
     */
    public static function matrizDePermisos(): array
    {
        $todos = Rol::cases();
        $personal = [Rol::Administrador, Rol::Recepcionista];

        return [
            'panel principal' => ['home', $todos],
            'listado de citas' => ['citas.index', $todos],
            'registrar cita' => ['citas.create', [...$personal, Rol::Paciente]],
            'pacientes' => ['admin.pacientes.index', $personal],
            'nuevo paciente' => ['admin.pacientes.create', $personal],
            'pagos' => ['pagos.index', $personal],
            'reportes' => ['reportes.index', $personal],
            'psicologos' => ['admin.psicologos.index', [Rol::Administrador]],
            'promociones' => ['admin.promociones.index', [Rol::Administrador]],
            'auditoria' => ['admin.auditoria.index', [Rol::Administrador]],
            'disponibilidad del psicologo' => ['psicologo.horarios.index', [Rol::Psicologo]],
        ];
    }

    /**
     * @param  list<Rol>  $permitidos
     */
    #[DataProvider('matrizDePermisos')]
    public function test_cada_rol_solo_accede_a_sus_modulos(string $ruta, array $permitidos): void
    {
        foreach (Rol::cases() as $rol) {
            $respuesta = $this->actingAs($this->usuarioCon($rol))->get(route($ruta));

            in_array($rol, $permitidos, true)
                ? $respuesta->assertOk()
                : $respuesta->assertForbidden();
        }
    }

    #[DataProvider('matrizDePermisos')]
    public function test_un_invitado_es_enviado_al_login(string $ruta): void
    {
        $this->get(route($ruta))->assertRedirect(route('login'));
    }

    public function test_el_paciente_solo_ve_sus_propias_citas(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $propia = $this->citaPara($psicologo, $this->fichaDe($usuario), ['hora' => '09:00']);
        $ajena = $this->citaPara($psicologo, atributos: ['hora' => '11:00']);

        $this->actingAs($usuario)->get(route('citas.show', $propia))->assertOk();
        $this->actingAs($usuario)->get(route('citas.show', $ajena))->assertForbidden();

        $this->actingAs($usuario)->get(route('citas.index'))
            ->assertOk()
            ->assertSee(route('citas.show', $propia))
            ->assertDontSee(route('citas.show', $ajena));
    }

    public function test_el_psicologo_solo_ve_las_citas_de_su_agenda(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        [$otro] = $this->psicologoConAgenda();
        $suya = $this->citaPara($psicologo);
        $ajena = $this->citaPara($otro);

        $this->actingAs($psicologo)->get(route('citas.show', $suya))->assertOk();
        $this->actingAs($psicologo)->get(route('citas.show', $ajena))->assertForbidden();

        $this->actingAs($psicologo)->get(route('citas.index'))
            ->assertSee(route('citas.show', $suya))
            ->assertDontSee(route('citas.show', $ajena));
    }

    public function test_el_personal_ve_todas_las_citas(): void
    {
        $cita = Cita::factory()->create();

        foreach ([Rol::Administrador, Rol::Recepcionista] as $rol) {
            $this->actingAs($this->usuarioCon($rol))->get(route('citas.show', $cita))->assertOk();
        }
    }

    public function test_un_paciente_sin_ficha_no_puede_solicitar_citas(): void
    {
        $usuario = User::factory()->create(['role' => Rol::Paciente]); // sin ficha de paciente

        $this->actingAs($usuario)->get(route('citas.create'))->assertForbidden();
        $this->actingAs($usuario)->get(route('home'))->assertOk()->assertSee('no tiene una ficha de paciente');
    }

    public function test_un_usuario_autenticado_no_ve_el_login(): void
    {
        $this->actingAs(User::factory()->administrador()->create())
            ->get(route('login'))
            ->assertRedirect(route('home'));
    }

    public function test_la_raiz_redirige_al_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }
}
