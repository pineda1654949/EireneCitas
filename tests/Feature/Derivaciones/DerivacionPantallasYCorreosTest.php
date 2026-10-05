<?php

namespace Tests\Feature\Derivaciones;

use App\Enums\EstadoDerivacion;
use App\Models\Cita;
use App\Models\Derivacion;
use App\Models\Especialidad;
use App\Models\Paciente;
use App\Models\User;
use App\Notifications\CitaCancelada;
use App\Notifications\CitaReprogramada;
use App\Notifications\DerivacionRecibida;
use App\Notifications\DerivacionRespondida;
use App\Notifications\RecordatorioDeCita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * RF-05: pantallas de derivacion y contenido de los correos.
 */
class DerivacionPantallasYCorreosTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_clinica_lista_y_filtra_las_derivaciones(): void
    {
        $pendiente = Derivacion::factory()->create();
        $rechazada = Derivacion::factory()->rechazada()->create();

        $this->actingAs(User::factory()->recepcionista()->create())
            ->get(route('admin.derivaciones.index'))
            ->assertOk()
            ->assertSee($pendiente->paciente->nombre_completo)
            ->assertSee('No atiendo casos de esta especialidad.');

        $this->get(route('admin.derivaciones.index', ['estado' => 'rechazada']))
            ->assertOk()
            ->assertSee($rechazada->paciente->nombre_completo)
            ->assertDontSee($pendiente->paciente->nombre_completo);
    }

    public function test_el_psicologo_ve_sus_derivaciones_con_las_pendientes_primero(): void
    {
        $psicologo = User::factory()->psicologo()->create();
        Derivacion::factory()->aceptada()->create(['psicologo_id' => $psicologo->id]);
        $pendiente = Derivacion::factory()->create(['psicologo_id' => $psicologo->id]);
        $ajena = Derivacion::factory()->create();

        $this->actingAs($psicologo)
            ->get(route('psicologo.derivaciones.index'))
            ->assertOk()
            ->assertSeeInOrder([$pendiente->paciente->nombre_completo, 'Aceptada'])
            ->assertSee('Rechazar')
            ->assertDontSee($ajena->paciente->nombre_completo);

        $this->get(route('home'))->assertViewHas('derivacionesPendientes', 1);
    }

    public function test_no_se_deriva_dos_veces_un_paciente_con_derivacion_pendiente(): void
    {
        Notification::fake();
        $paciente = Paciente::factory()->create();
        Derivacion::factory()->create(['paciente_id' => $paciente->id]);
        $psicologo = User::factory()->psicologo()->create();

        $this->actingAs(User::factory()->administrador()->create())
            ->post(route('admin.pacientes.derivar', $paciente), ['psicologo_id' => $psicologo->id])
            ->assertSessionHasErrors(['psicologo_id' => 'El paciente ya tiene una derivación pendiente de respuesta.']);
    }

    public function test_no_se_deriva_a_un_psicologo_inactivo_ni_de_otra_especialidad(): void
    {
        Notification::fake();
        $paciente = Paciente::factory()->create();
        $inactivo = User::factory()->psicologo()->inactivo()->create();
        $activo = User::factory()->psicologo()->create();
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)
            ->post(route('admin.pacientes.derivar', $paciente), ['psicologo_id' => $inactivo->id])
            ->assertSessionHasErrors('psicologo_id');

        $this->actingAs($admin)
            ->post(route('admin.pacientes.derivar', $paciente), ['psicologo_id' => $activo->id, 'especialidad_id' => Especialidad::factory()->create()->id])
            ->assertSessionHasErrors('especialidad_id');

        $this->assertDatabaseCount('derivaciones', 0);
    }

    public function test_el_psicologo_derivado_puede_ver_la_historia_clinica(): void
    {
        $derivacion = Derivacion::factory()->aceptada()->create();
        $derivacion->paciente->update(['psicologo_id' => $derivacion->psicologo_id]);

        $this->actingAs($derivacion->psicologo)
            ->get(route('psicologo.historial.paciente', $derivacion->paciente))
            ->assertOk();
    }

    public function test_los_correos_de_derivacion_tienen_los_datos_del_caso(): void
    {
        $derivacion = Derivacion::factory()->rechazada()->create();

        $recibida = (new DerivacionRecibida($derivacion))->toMail($derivacion->psicologo);
        $this->assertStringContainsString('Nueva derivación de paciente', $recibida->subject);
        $this->assertStringContainsString('/psicologo/derivaciones', (string) $recibida->actionUrl);

        $respondida = (new DerivacionRespondida($derivacion))->toMail($derivacion->derivadoPor);
        $this->assertStringContainsString('Derivación rechazada', $respondida->subject);
        $this->assertStringContainsString('No atiendo casos de esta especialidad.', implode(' ', $respondida->introLines));
        $this->assertSame(['mail'], (new DerivacionRespondida($derivacion))->via($derivacion->derivadoPor));
        $this->assertSame(EstadoDerivacion::Rechazada->tono(), 'rose');
    }

    /**
     * @return array<string, array{class-string, string}>
     */
    public static function correosDeCita(): array
    {
        return [
            'reprogramada' => [CitaReprogramada::class, 'Cita reprogramada'],
            'cancelada' => [CitaCancelada::class, 'Cita cancelada'],
            'recordatorio' => [RecordatorioDeCita::class, 'Recordatorio de cita'],
        ];
    }

    /**
     * @param  class-string  $clase
     */
    #[DataProvider('correosDeCita')]
    public function test_cada_correo_de_cita_tiene_su_asunto(string $clase, string $asunto): void
    {
        $cita = Cita::factory()->create();

        $correo = (new $clase($cita))->toMail($cita->paciente);

        $this->assertStringStartsWith($asunto, $correo->subject);
    }
}
