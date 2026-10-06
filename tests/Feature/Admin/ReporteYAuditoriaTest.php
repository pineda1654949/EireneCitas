<?php

namespace Tests\Feature\Admin;

use App\Enums\EstadoCita;
use App\Models\Auditoria;
use App\Models\Cita;
use App\Models\HistorialClinico;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * RF-08 (reportes) y trazabilidad de acciones (auditoria).
 */
class ReporteYAuditoriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_reporte_resume_las_citas_del_periodo(): void
    {
        $psicologo = User::factory()->psicologo()->create(['name' => 'Maria', 'apellidos' => 'Fernandez']);
        $enRango = ['psicologo_id' => $psicologo->id, 'fecha' => '2026-10-01'];

        Cita::factory()->atendida()->create($enRango);
        Cita::factory()->cancelada()->create($enRango + ['numero_reprogramaciones' => 2]);
        $conPago = Cita::factory()->confirmada()->create($enRango);
        Pago::factory()->confirmado()->create(['cita_id' => $conPago->id, 'monto' => 120]);
        Cita::factory()->create(['psicologo_id' => $psicologo->id, 'fecha' => '2026-01-15']); // fuera de rango

        $respuesta = $this->actingAs(User::factory()->administrador()->create())
            ->get(route('reportes.index', ['desde' => '2026-09-01', 'hasta' => '2026-10-31']));

        $respuesta->assertOk()
            ->assertViewHas('totalCitas', 3)
            ->assertViewHas('totalReprogramaciones', 2)
            ->assertViewHas('ingresosConfirmados', fn ($monto) => (float) $monto === 120.0)
            ->assertSee('Maria Fernandez')
            ->assertSee('S/ 120.00');
    }

    public function test_valida_el_rango_de_fechas(): void
    {
        $this->actingAs(User::factory()->administrador()->create())
            ->get(route('reportes.index', ['desde' => '2026-10-10', 'hasta' => '2026-10-01']))
            ->assertSessionHasErrors('hasta');
    }

    public function test_exporta_las_citas_a_csv(): void
    {
        $cita = Cita::factory()->create(['fecha' => Carbon::now()->toDateString(), 'estado' => EstadoCita::Confirmada]);

        $respuesta = $this->actingAs(User::factory()->recepcionista()->create())
            ->get(route('reportes.exportar'));

        $respuesta->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $csv = $respuesta->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Paciente', $csv);
        $this->assertStringContainsString($cita->paciente->nombre_completo, $csv);
        $this->assertStringContainsString('Confirmada', $csv);
    }

    public function test_registra_en_auditoria_los_cambios_con_usuario_y_valores(): void
    {
        $admin = User::factory()->administrador()->create();
        $cita = Cita::factory()->create();

        $this->actingAs($admin);
        $cita->update(['estado' => EstadoCita::Cancelada]);

        $registro = Auditoria::where('evento', 'actualizado')->where('auditable_type', Cita::class)->sole();
        $this->assertSame($admin->id, $registro->user_id);
        $this->assertSame($cita->id, $registro->auditable_id);
        $this->assertSame(['estado' => 'pendiente'], $registro->valores_anteriores);
        $this->assertSame(['estado' => 'cancelada'], $registro->valores_nuevos);
    }

    public function test_la_auditoria_nunca_guarda_contrasenas_ni_notas_clinicas(): void
    {
        $usuario = User::factory()->create(['password' => 'Secreta999']);
        HistorialClinico::factory()->create(['notas_sesion' => 'Contenido confidencial']);

        $volcado = Auditoria::all()->toJson();

        $this->assertStringNotContainsString('Secreta999', $volcado);
        $this->assertStringNotContainsString((string) $usuario->getAuthPassword(), $volcado);
        $this->assertStringNotContainsString('confidencial', $volcado);
        $this->assertStringContainsString('[oculto]', $volcado);
    }

    public function test_registra_inicios_de_sesion_e_intentos_fallidos(): void
    {
        $usuario = User::factory()->create(['email' => 'ana@eirene.test']);

        $this->post(route('login'), ['email' => 'ana@eirene.test', 'password' => 'mala']);
        $this->post(route('login'), ['email' => 'ana@eirene.test', 'password' => 'Secreta123']);

        $this->assertDatabaseHas('auditorias', ['evento' => 'inicio_sesion_fallido', 'user_id' => $usuario->id]);
        $this->assertDatabaseHas('auditorias', ['evento' => 'inicio_sesion', 'user_id' => $usuario->id]);
    }

    public function test_el_administrador_consulta_y_filtra_la_auditoria(): void
    {
        $admin = User::factory()->administrador()->create(['name' => 'Auditor']);
        $this->actingAs($admin);
        Cita::factory()->create();

        $this->get(route('admin.auditoria.index', ['tipo' => 'Cita', 'evento' => 'creado']))
            ->assertOk()
            ->assertSee('Cita #')
            ->assertSee('Auditor');

        $this->get(route('admin.auditoria.index', ['tipo' => 'NoExiste']))->assertSessionHasErrors('tipo');
    }

    public function test_los_registros_antiguos_se_depuran(): void
    {
        Auditoria::registrar('creado');
        $this->travel(400)->days();
        Auditoria::registrar('creado');

        $this->artisan('model:prune', ['--model' => [Auditoria::class]])->assertSuccessful();

        $this->assertSame(1, Auditoria::count());
    }
}
