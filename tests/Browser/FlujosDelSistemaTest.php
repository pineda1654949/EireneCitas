<?php

namespace Tests\Browser;

use App\Enums\EstadoCita;
use App\Models\Cita;
use App\Models\Derivacion;
use App\Models\HistorialClinico;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Fase 8 - System Testing. Flujos de extremo a extremo en Chrome real
 * (CP-SYS-01 a CP-SYS-10 y CP-SYS-12). Laravel 12 + Laravel Dusk.
 */
class FlujosDelSistemaTest extends DuskTestCase
{
    use DatabaseTruncation;

    private function pacienteConCuenta(string $nombre = 'Ana', string $email = 'ana@correo.pe'): User
    {
        return User::factory()->paciente()->create(['name' => $nombre, 'apellidos' => 'Torres', 'email' => $email]);
    }

    public function test_CP_SYS_01_recepcionista_registra_paciente_y_agenda_cita_con_pago(): void
    {
        $recepcion = User::factory()->recepcionista()->create(['email' => 'recepcion@eirene.test']);
        [$psicologo, $especialidad] = $this->psicologoConAgenda();

        $this->browse(function (Browser $browser) use ($recepcion, $psicologo, $especialidad) {
            $this->iniciarSesion($browser, $recepcion->email)
                ->visit('/admin/pacientes/create')
                ->type('nombres', 'Lucia')
                ->type('apellidos', 'Quispe Mamani')
                ->type('dni', '71234567')
                ->type('correo', 'lucia@correo.pe')
                ->type('telefono', '987654321')
                ->press('Guardar paciente')
                ->waitForText('Paciente registrado correctamente.')
                ->screenshot('CP-SYS-01a-paciente-registrado');

            $paciente = Paciente::where('dni', '71234567')->sole();

            $browser->visit('/citas/crear')
                ->select('paciente_id', (string) $paciente->id)
                ->select('especialidad_id', (string) $especialidad->id)
                ->waitFor("select[name=psicologo_id] option[value='{$psicologo->id}']")
                ->select('psicologo_id', (string) $psicologo->id);

            $this->elegirFecha($browser, $this->proximoLunes());
            $this->elegirHora($browser, '10:00')
                ->type('motivo_consulta', 'Ansiedad laboral')
                ->screenshot('CP-SYS-01b-matriz-de-horas')
                ->press('Registrar cita')
                ->waitForText('Cita registrada correctamente.')
                ->clickLink('Registrar pago')
                ->waitForText('Voucher del pago')
                ->type('monto', '80')
                ->select('metodo_pago', 'yape_plin')
                ->attach('comprobante', __DIR__.'/fixtures/voucher.png')
                ->press('Registrar pago')
                ->waitForText('Pago registrado, pendiente de validación.')
                ->assertSee('Ver voucher')
                ->screenshot('CP-SYS-01c-pago-registrado');
        });

        $cita = Cita::sole();
        $this->assertSame('10:00', $cita->hora_corta);
        $this->assertNotNull($cita->pagos()->sole()->comprobante_path);
    }

    public function test_CP_SYS_02_recepcionista_valida_el_pago_y_la_cita_queda_confirmada(): void
    {
        $recepcion = User::factory()->recepcionista()->create(['email' => 'recepcion@eirene.test']);
        [$psicologo] = $this->psicologoConAgenda();
        $cita = Cita::factory()->create(['psicologo_id' => $psicologo->id, 'fecha' => $this->proximoLunes(), 'hora' => '10:00']);
        Pago::factory()->create(['cita_id' => $cita->id, 'monto' => 80]);

        $this->browse(function (Browser $browser) use ($recepcion, $cita) {
            $this->iniciarSesion($browser, $recepcion->email)
                ->visit("/citas/{$cita->id}")
                ->assertSee('Pendiente')
                ->press('Validar')
                ->waitForText('Pago validado y cita confirmada.')
                ->assertSee('Confirmada')
                ->screenshot('CP-SYS-02-cita-confirmada');
        });

        $this->assertSame(EstadoCita::Confirmada, $cita->fresh()?->estado);
    }

    public function test_CP_SYS_03_reprogramacion_al_limite_bloquea_el_cuarto_cambio_con_aviso(): void
    {
        $usuario = $this->pacienteConCuenta();
        [$psicologo] = $this->psicologoConAgenda();
        $cita = Cita::factory()->create([
            'psicologo_id' => $psicologo->id, 'paciente_id' => $usuario->pacienteFicha?->id,
            'fecha' => $this->proximoLunes(), 'hora' => '10:00', 'numero_reprogramaciones' => 2,
        ]);

        $this->browse(function (Browser $browser) use ($usuario, $cita) {
            $this->iniciarSesion($browser, $usuario->email)
                ->visit("/citas/{$cita->id}")
                ->clickLink('Reprogramar')
                ->waitForText('Nueva fecha y hora');

            $this->elegirFecha($browser, $this->proximoLunes(1));
            $this->elegirHora($browser, '11:00')
                ->type('motivo', 'Viaje de trabajo')
                ->press('Reprogramar')
                ->waitForText('Cita reprogramada correctamente.')
                ->assertSee('Se alcanzó el límite de 3 reprogramaciones')
                ->assertDontSeeLink('Reprogramar')
                ->screenshot('CP-SYS-03a-limite-alcanzado')
                ->visit("/citas/{$cita->id}/reprogramar")
                ->waitForText('Un nuevo cambio requiere la autorización del administrador')
                ->screenshot('CP-SYS-03b-cuarto-cambio-bloqueado');
        });

        $this->assertSame(3, $cita->fresh()?->numero_reprogramaciones);
    }

    public function test_CP_SYS_04_derivacion_del_paciente_al_psicologo(): void
    {
        $recepcion = User::factory()->recepcionista()->create(['email' => 'recepcion@eirene.test']);
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $paciente = Paciente::factory()->create(['nombres' => 'Jorge', 'apellidos' => 'Salas']);

        $this->browse(function (Browser $clinica, Browser $profesional) use ($recepcion, $psicologo, $especialidad, $paciente) {
            $this->iniciarSesion($clinica, $recepcion->email)
                ->visit("/admin/pacientes/{$paciente->id}/edit")
                ->select('psicologo_id', (string) $psicologo->id)
                ->select('especialidad_id', (string) $especialidad->id)
                ->type('observaciones', 'Refiere ansiedad desde hace 3 meses')
                ->press('Derivar paciente')
                ->waitForText('Paciente derivado.');

            $this->iniciarSesion($profesional, $psicologo->email)
                ->visit('/psicologo/derivaciones')
                ->assertSee('Jorge Salas')
                ->assertSee('Refiere ansiedad desde hace 3 meses')
                ->press('Aceptar')
                ->waitForText('Derivación aceptada.')
                ->screenshot('CP-SYS-04a-derivacion-aceptada');

            $clinica->visit("/admin/pacientes/{$paciente->id}/edit")
                ->assertSee('Maria Fernandez')
                ->assertSee('Aceptada')
                ->screenshot('CP-SYS-04b-paciente-asignado');
        });

        $this->assertSame($psicologo->id, $paciente->fresh()?->psicologo_id);
    }

    public function test_CP_SYS_05_rechazo_de_derivacion_con_motivo(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $derivacion = Derivacion::factory()->create(['psicologo_id' => $psicologo->id]);

        $this->browse(function (Browser $browser) use ($psicologo, $derivacion) {
            $this->iniciarSesion($browser, $psicologo->email)
                ->visit('/psicologo/derivaciones')
                ->type("#motivo-{$derivacion->id}", 'No atiendo casos de terapia infantil')
                ->press('Rechazar')
                ->waitForText('Derivación rechazada.')
                ->assertSee('No atiendo casos de terapia infantil')
                ->screenshot('CP-SYS-05-derivacion-rechazada');
        });

        $this->assertSame('rechazada', $derivacion->fresh()?->estado->value);
    }

    public function test_CP_SYS_06_dos_usuarios_eligen_la_misma_hora_y_solo_uno_reserva(): void
    {
        [$psicologo, $especialidad] = $this->psicologoConAgenda();
        $ana = $this->pacienteConCuenta('Ana', 'ana@correo.pe');
        $luis = $this->pacienteConCuenta('Luis', 'luis@correo.pe');

        $this->browse(function (Browser $primero, Browser $segundo) use ($ana, $luis, $psicologo, $especialidad) {
            foreach ([[$primero, $ana], [$segundo, $luis]] as [$browser, $usuario]) {
                $this->iniciarSesion($browser, $usuario->email)
                    ->visit('/citas/crear')
                    ->select('especialidad_id', (string) $especialidad->id)
                    ->waitFor("select[name=psicologo_id] option[value='{$psicologo->id}']")
                    ->select('psicologo_id', (string) $psicologo->id);
                $this->elegirFecha($browser, $this->proximoLunes());
                $this->elegirHora($browser, '10:00');
            }

            // Ambos ven las 10:00 libres; el primero en enviar se queda con la hora.
            $primero->press('Registrar cita')->waitForText('Cita registrada correctamente.');

            $segundo->press('Registrar cita')
                ->waitForText('Ese horario no está disponible para el psicólogo')
                ->screenshot('CP-SYS-06-segundo-usuario-avisado');
        });

        $this->assertSame(1, Cita::count());
        $this->assertSame($ana->pacienteFicha?->id, Cita::sole()->paciente_id);
    }

    public function test_CP_SYS_07_historial_registrado_es_visible_solo_para_el_psicologo(): void
    {
        $recepcion = User::factory()->recepcionista()->create(['email' => 'recepcion@eirene.test']);
        [$psicologo] = $this->psicologoConAgenda();
        $cita = Cita::factory()->confirmada()->create(['psicologo_id' => $psicologo->id, 'fecha' => now()->toDateString(), 'hora' => '09:00']);

        $this->browse(function (Browser $profesional, Browser $clinica) use ($psicologo, $recepcion, $cita) {
            $this->iniciarSesion($profesional, $psicologo->email)
                ->visit("/citas/{$cita->id}")
                ->clickLink('Atender sesión')
                ->type('notas_sesion', 'Paciente refiere mejoria en el descanso nocturno.')
                ->select('avance', 'En progreso')
                ->press('Guardar y marcar como atendida')
                ->waitForText('Historial clínico registrado.')
                ->assertSee('Paciente refiere mejoria en el descanso nocturno.')
                ->screenshot('CP-SYS-07a-historial-del-psicologo');

            $this->iniciarSesion($clinica, $recepcion->email)
                ->visit("/citas/{$cita->id}")
                ->assertSee('Atendida')
                ->assertDontSee('Paciente refiere mejoria')
                ->screenshot('CP-SYS-07b-recepcion-no-ve-notas');
        });

        $this->assertSame(1, HistorialClinico::count());
    }

    public function test_CP_SYS_08_el_estado_de_la_cita_es_coherente_en_todas_las_vistas(): void
    {
        $recepcion = User::factory()->recepcionista()->create(['email' => 'recepcion@eirene.test']);
        $usuario = $this->pacienteConCuenta();
        [$psicologo] = $this->psicologoConAgenda();
        $cita = Cita::factory()->create([
            'psicologo_id' => $psicologo->id, 'paciente_id' => $usuario->pacienteFicha?->id,
            'fecha' => $this->proximoLunes(), 'hora' => '10:00',
        ]);
        Pago::factory()->create(['cita_id' => $cita->id]);

        $this->browse(function (Browser $clinica, Browser $paciente) use ($recepcion, $usuario, $cita) {
            $this->iniciarSesion($clinica, $recepcion->email)->visit("/citas/{$cita->id}")->press('Validar')->waitForText('Pago validado');

            $this->iniciarSesion($paciente, $usuario->email)
                ->assertSee('Tu próxima sesión')->assertSee('Confirmada')
                ->visit('/citas')->assertSee('Confirmada')
                ->visit("/citas/{$cita->id}")->assertSee('Confirmada')
                ->clickLink('Cancelar cita')
                ->type('motivo', 'Ya no puedo asistir')
                ->press('Sí, cancelar cita')
                ->waitForText('Cita cancelada correctamente.')
                ->assertSee('Cancelada')
                ->visit('/citas')->assertSee('Cancelada')
                ->screenshot('CP-SYS-08-cancelada-en-listado');

            $clinica->visit('/citas')->assertSee('Cancelada');
        });

        $this->assertSame(EstadoCita::Cancelada, $cita->fresh()?->estado);
    }

    public function test_CP_SYS_09_un_error_de_validacion_no_pierde_lo_escrito(): void
    {
        $recepcion = User::factory()->recepcionista()->create(['email' => 'recepcion@eirene.test']);
        Paciente::factory()->create(['dni' => '71234567']);

        $this->browse(function (Browser $browser) use ($recepcion) {
            $this->iniciarSesion($browser, $recepcion->email)
                ->visit('/admin/pacientes/create')
                ->type('nombres', 'Rosa Elena')
                ->type('apellidos', 'Huaman')
                ->type('dni', '71234567')
                ->type('direccion', 'Av. Arequipa 123')
                ->press('Guardar paciente')
                ->waitForText('El DNI ya está registrado.')
                ->assertInputValue('nombres', 'Rosa Elena')
                ->assertInputValue('apellidos', 'Huaman')
                ->assertInputValue('direccion', 'Av. Arequipa 123')
                ->screenshot('CP-SYS-09-error-conserva-datos');
        });
    }

    public function test_CP_SYS_10_los_totales_del_dashboard_coinciden_con_la_base_de_datos(): void
    {
        $admin = User::factory()->administrador()->create(['email' => 'admin']);
        Paciente::factory()->count(4)->create();
        Cita::factory()->count(3)->create();
        Cita::factory()->confirmada()->count(2)->create();

        $pendientes = Cita::where('estado', 'pendiente')->count();

        $this->browse(function (Browser $browser) use ($admin, $pendientes) {
            $this->iniciarSesion($browser, $admin->email)
                // Tarjetas de indicadores del panel (no los enlaces del menu lateral).
                ->assertSeeIn('main a.card[href$="/admin/pacientes"]', (string) Paciente::count())
                ->assertSeeIn('main a.card[href*="estado=pendiente"]', (string) $pendientes)
                ->screenshot('CP-SYS-10-dashboard');
        });

        $this->assertSame(3, $pendientes);
    }

    public function test_CP_SYS_12_sesion_expirada_redirige_al_login(): void
    {
        $recepcion = User::factory()->recepcionista()->create(['email' => 'recepcion@eirene.test']);

        $this->browse(function (Browser $browser) use ($recepcion) {
            $this->iniciarSesion($browser, $recepcion->email)
                ->assertPathIs('/home')
                // Al expirar, el navegador deja de enviar la cookie de sesion.
                ->deleteCookie(config('session.cookie'))
                ->visit('/admin/pacientes')
                ->assertPathIs('/login')
                ->screenshot('CP-SYS-12-sesion-expirada');
        });
    }
}
