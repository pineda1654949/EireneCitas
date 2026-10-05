<?php

namespace Tests\Feature\Servicios;

use App\Enums\EstadoCita;
use App\Models\Horario;
use App\Services\AgendaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * RF-02: consulta de disponibilidad de horarios en tiempo real.
 */
class AgendaServiceTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    private AgendaService $agenda;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agenda = app(AgendaService::class);
    }

    public function test_genera_sesiones_de_50_minutos_cada_hora_dentro_del_bloque(): void
    {
        [$psicologo] = $this->psicologoConAgenda();

        $this->assertSame(
            ['09:00', '10:00', '11:00', '12:00'],
            $this->agenda->horasDisponibles($psicologo->id, $this->proximoLunes()),
        );
    }

    public function test_no_ofrece_una_sesion_que_no_cabe_completa_en_el_bloque(): void
    {
        // 09:00-11:40 -> 09:00 y 10:00 caben; 11:00 terminaria 11:50 (fuera del bloque).
        [$psicologo] = $this->psicologoConAgenda(inicio: '09:00', fin: '11:40');

        $this->assertSame(['09:00', '10:00'], $this->agenda->horasDisponibles($psicologo->id, $this->proximoLunes()));
    }

    public function test_combina_varios_bloques_del_mismo_dia_en_orden(): void
    {
        [$psicologo] = $this->psicologoConAgenda(inicio: '15:00', fin: '17:00');
        Horario::factory()->create(['psicologo_id' => $psicologo->id, 'dia_semana' => 1, 'hora_inicio' => '09:00', 'hora_fin' => '10:00']);

        $this->assertSame(['09:00', '15:00', '16:00'], $this->agenda->horasDisponibles($psicologo->id, $this->proximoLunes()));
    }

    /**
     * Particion de equivalencia sobre el estado de la cita existente.
     *
     * @return array<string, array{EstadoCita, bool}>
     */
    public static function estadosQueOcupan(): array
    {
        return [
            'pendiente ocupa' => [EstadoCita::Pendiente, true],
            'confirmada ocupa' => [EstadoCita::Confirmada, true],
            'reprogramada ocupa' => [EstadoCita::Reprogramada, true],
            'cancelada libera' => [EstadoCita::Cancelada, false],
            'atendida no aplica (sesion pasada)' => [EstadoCita::Atendida, false],
        ];
    }

    #[DataProvider('estadosQueOcupan')]
    public function test_una_cita_ocupa_el_horario_segun_su_estado(EstadoCita $estado, bool $ocupa): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $this->citaPara($psicologo, atributos: ['hora' => '10:00', 'estado' => $estado]);

        $horas = $this->agenda->horasDisponibles($psicologo->id, $this->proximoLunes());

        $this->assertSame(! $ocupa, in_array('10:00', $horas, true));
    }

    public function test_al_reprogramar_la_hora_propia_cuenta_como_libre(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['hora' => '10:00']);

        $this->assertNotContains('10:00', $this->agenda->horasDisponibles($psicologo->id, $this->proximoLunes()));
        $this->assertContains('10:00', $this->agenda->horasDisponibles($psicologo->id, $this->proximoLunes(), $cita->id));
    }

    public function test_las_citas_de_otro_psicologo_no_afectan(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        [$otro] = $this->psicologoConAgenda();
        $this->citaPara($otro, atributos: ['hora' => '10:00']);

        $this->assertContains('10:00', $this->agenda->horasDisponibles($psicologo->id, $this->proximoLunes()));
    }

    public function test_un_bloque_pausado_no_ofrece_horas(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        Horario::where('psicologo_id', $psicologo->id)->update(['activo' => false]);

        $this->assertSame([], $this->agenda->horasDisponibles($psicologo->id, $this->proximoLunes()));
    }

    public function test_un_dia_sin_bloques_no_tiene_horas(): void
    {
        [$psicologo] = $this->psicologoConAgenda(diaSemana: Carbon::MONDAY);
        $martes = Carbon::parse($this->proximoLunes())->addDay()->toDateString();

        $this->assertSame([], $this->agenda->horasDisponibles($psicologo->id, $martes));
    }

    public function test_una_fecha_pasada_no_tiene_horas(): void
    {
        [$psicologo] = $this->psicologoConAgenda();

        $this->assertSame([], $this->agenda->horasDisponibles($psicologo->id, Carbon::now()->subWeek()->toDateString()));
    }

    public function test_hoy_solo_ofrece_horas_con_la_anticipacion_minima(): void
    {
        // Ahora: lunes 08:00. Con 60 min de anticipacion, 09:00 ya es reservable; con 90, no.
        [$psicologo] = $this->psicologoConAgenda();
        $hoy = Carbon::now()->toDateString();

        $this->assertSame(['09:00', '10:00', '11:00', '12:00'], $this->agenda->horasDisponibles($psicologo->id, $hoy));

        config(['eirene.citas.anticipacion_minima_minutos' => 90]);
        $this->assertSame(['10:00', '11:00', '12:00'], $this->agenda->horasDisponibles($psicologo->id, $hoy));

        $this->travelTo(Carbon::parse('2026-10-05 11:30'));
        $this->assertSame([], $this->agenda->horasDisponibles($psicologo->id, $hoy));
    }

    public function test_un_psicologo_inactivo_no_tiene_disponibilidad(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $psicologo->update(['activo' => false]);

        $this->assertSame([], $this->agenda->horasDisponibles($psicologo->id, $this->proximoLunes()));
    }

    public function test_la_duracion_y_el_intervalo_son_configurables(): void
    {
        config(['eirene.citas.duracion_minutos' => 30, 'eirene.citas.intervalo_minutos' => 30]);
        [$psicologo] = $this->psicologoConAgenda(inicio: '09:00', fin: '10:30');

        $this->assertSame(['09:00', '09:30', '10:00'], $this->agenda->horasDisponibles($psicologo->id, $this->proximoLunes()));
    }

    public function test_esta_disponible_acepta_horas_con_segundos(): void
    {
        [$psicologo] = $this->psicologoConAgenda();

        $this->assertTrue($this->agenda->estaDisponible($psicologo->id, $this->proximoLunes(), '09:00:00'));
        $this->assertFalse($this->agenda->estaDisponible($psicologo->id, $this->proximoLunes(), '09:30'));
    }
}
