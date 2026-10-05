<?php

namespace Tests\Feature;

use App\Enums\EstadoCita;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * Pruebas de regresion de los defectos encontrados en la revision extensiva
 * (DEF-009 a DEF-015). Cada prueba de defecto fallaba antes de su correccion.
 */
class RevisionDefectosTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    /** DEF-009: un correo con mayusculas se rechazaba en el registro. */
    public function test_el_registro_acepta_correos_con_mayusculas_y_los_normaliza(): void
    {
        $this->post(route('register'), [
            'name' => 'Ana', 'apellidos' => 'Rios', 'email' => '  Ana.Rios@Correo.PE ',
            'password' => 'Segura2026', 'password_confirmation' => 'Segura2026',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'ana.rios@correo.pe']);
    }

    /** DEF-009: los mensajes de la politica de contrasenas (y otras 36 reglas) salian en ingles. */
    public function test_los_mensajes_de_la_politica_de_contrasenas_estan_en_espanol(): void
    {
        $this->post(route('register'), [
            'name' => 'Ana', 'apellidos' => 'Rios', 'email' => 'ana@correo.pe',
            'password' => 'SoloLetras', 'password_confirmation' => 'SoloLetras',
        ])->assertSessionHasErrors(['password' => 'El campo contraseña debe contener al menos un número.']);
    }

    /** DEF-010: un paciente podia tener dos citas a la misma hora con distintos psicologos. */
    public function test_un_paciente_no_puede_tener_dos_citas_a_la_misma_hora(): void
    {
        [$psicologoA, $especialidadA] = $this->psicologoConAgenda();
        [$psicologoB, $especialidadB] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $this->citaPara($psicologoA, $this->fichaDe($usuario), ['hora' => '10:00']);

        $this->actingAs($usuario)->post(route('citas.store'), [
            'especialidad_id' => $especialidadB->id,
            'psicologo_id' => $psicologoB->id,
            'fecha' => $this->proximoLunes(),
            'hora' => '10:00',
        ])->assertSessionHasErrors('hora');

        $this->assertSame(1, Cita::count());
        $this->assertNotNull($especialidadA);
    }

    /** DEF-010: tampoco al reprogramar hacia la hora de otra cita propia. */
    public function test_no_reprograma_hacia_la_hora_de_otra_cita_del_mismo_paciente(): void
    {
        [$psicologoA] = $this->psicologoConAgenda();
        [$psicologoB] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $paciente = $this->fichaDe($usuario);
        $this->citaPara($psicologoA, $paciente, ['hora' => '11:00']);
        $cita = $this->citaPara($psicologoB, $paciente, ['hora' => '09:00']);

        $this->actingAs($usuario)
            ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '11:00'])
            ->assertSessionHasErrors('hora');
    }

    /** DEF-011: el registro publico duplicaba la ficha de un paciente ya registrado por recepcion. */
    public function test_el_registro_no_duplica_una_ficha_existente_con_el_mismo_dni(): void
    {
        Paciente::factory()->create(['dni' => '71234567']);

        $this->post(route('register'), [
            'name' => 'Ana', 'apellidos' => 'Rios', 'dni' => '71234567', 'email' => 'ana@correo.pe',
            'password' => 'Segura2026', 'password_confirmation' => 'Segura2026',
        ])->assertSessionHasErrors('dni');

        $this->assertSame(1, Paciente::count());
        $this->assertGuest();
    }

    /** DEF-012: el psicologo podia marcar como atendida una cita que aun no ocurre. */
    public function test_no_se_registra_la_sesion_de_una_cita_futura(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo); // el proximo lunes

        $this->actingAs($psicologo)
            ->post(route('psicologo.historial.store', $cita), ['notas_sesion' => 'Notas'])
            ->assertSessionHasErrors('notas_sesion');

        $this->assertSame(EstadoCita::Pendiente, $cita->refresh()->estado);
        $this->actingAs($psicologo)->get(route('citas.show', $cita))->assertDontSee('Atender sesión');
    }

    /** DEF-012: una cita de hoy si se puede atender. */
    public function test_se_registra_la_sesion_de_una_cita_de_hoy(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['fecha' => now()->toDateString(), 'hora' => '09:00']);

        $this->actingAs($psicologo)
            ->post(route('psicologo.historial.store', $cita), ['notas_sesion' => 'Notas'])
            ->assertSessionHasNoErrors();

        $this->assertSame(EstadoCita::Atendida, $cita->refresh()->estado);
    }

    /** Valor limite: el reporte incluye el primer y el ultimo dia del rango (se sospecho un defecto; la prueba lo descarto). */
    public function test_el_reporte_incluye_el_primer_y_el_ultimo_dia_del_rango(): void
    {
        Cita::factory()->create(['fecha' => '2026-09-01']);
        Cita::factory()->create(['fecha' => '2026-09-30']);

        $this->actingAs(User::factory()->administrador()->create())
            ->get(route('reportes.index', ['desde' => '2026-09-01', 'hasta' => '2026-09-30']))
            ->assertViewHas('totalCitas', 2);
    }

    /** DEF-013: reprogramar a la misma fecha y hora consumia una de las 3 reprogramaciones. */
    public function test_reprogramar_sin_cambios_no_consume_una_reprogramacion(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $cita = $this->citaPara($psicologo, $this->fichaDe($usuario), ['hora' => '10:00']);

        $this->actingAs($usuario)
            ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '10:00'])
            ->assertSessionHasErrors('hora');

        $this->assertSame(0, $cita->refresh()->numero_reprogramaciones);
    }

    /** DEF-014: inyeccion de formulas en el CSV exportado (OWASP CSV Injection). */
    public function test_el_csv_neutraliza_formulas_de_excel(): void
    {
        $paciente = Paciente::factory()->create(['nombres' => '=HYPERLINK("http://malo")', 'apellidos' => 'X']);
        Cita::factory()->create(['paciente_id' => $paciente->id, 'fecha' => now()->toDateString()]);

        $csv = $this->actingAs(User::factory()->administrador()->create())
            ->get(route('reportes.exportar'))
            ->streamedContent();

        $this->assertStringNotContainsString(',"=HYPERLINK', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    /** DEF-015: "Tu proxima sesion" mostraba una cita lejana si el paciente tenia mas de 10 citas futuras. */
    public function test_el_panel_del_paciente_muestra_la_cita_futura_mas_cercana(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $paciente = $this->fichaDe($usuario);

        $cercana = $this->citaPara($psicologo, $paciente, ['fecha' => now()->addDay()->toDateString()]);
        foreach (range(2, 13) as $semanas) {
            $this->citaPara($psicologo, $paciente, ['fecha' => now()->addWeeks($semanas)->toDateString()]);
        }

        $this->actingAs($usuario)
            ->get(route('home'))
            ->assertViewHas('proximaCita', fn (?Cita $cita) => $cita?->is($cercana) === true);
    }
}
