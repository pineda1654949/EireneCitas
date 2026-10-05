<?php

namespace Tests\Unit\Models;

use App\Enums\EstadoCita;
use App\Models\Cita;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Reglas de negocio de la cita que no requieren base de datos
 * (RF-03 y RNF "Confiabilidad de reglas de negocio").
 */
class ReglasDeCitaTest extends TestCase
{
    private function cita(EstadoCita $estado = EstadoCita::Pendiente, int $reprogramaciones = 0): Cita
    {
        return new Cita([
            'fecha' => '2026-10-12',
            'hora' => '10:00:00',
            'estado' => $estado,
            'numero_reprogramaciones' => $reprogramaciones,
        ]);
    }

    public function test_el_limite_de_reprogramaciones_por_defecto_es_3(): void
    {
        $this->assertSame(3, Cita::maxReprogramaciones());
    }

    /**
     * Analisis de valores limite sobre el numero de reprogramaciones.
     *
     * @return array<string, array{int, bool, int}>
     */
    public static function valoresLimite(): array
    {
        return [
            'sin reprogramar' => [0, true, 3],
            'una vez' => [1, true, 2],
            'limite - 1' => [2, true, 1],
            'en el limite' => [3, false, 0],
            'sobre el limite' => [4, false, 0],
        ];
    }

    #[DataProvider('valoresLimite')]
    public function test_puede_reprogramarse_hasta_el_limite(int $usadas, bool $esperado, int $restantes): void
    {
        $cita = $this->cita(EstadoCita::Confirmada, $usadas);

        $this->assertSame($esperado, $cita->puedeReprogramarse());
        $this->assertSame($restantes, $cita->reprogramacionesRestantes());
    }

    public function test_el_limite_se_lee_de_la_configuracion(): void
    {
        config(['eirene.citas.max_reprogramaciones' => 5]);

        $this->assertTrue($this->cita(EstadoCita::Pendiente, 4)->puedeReprogramarse());
    }

    public function test_una_cita_cerrada_no_se_reprograma_ni_cancela(): void
    {
        foreach ([EstadoCita::Cancelada, EstadoCita::Atendida] as $estado) {
            $cita = $this->cita($estado);

            $this->assertFalse($cita->puedeReprogramarse(), $estado->value);
            $this->assertFalse($cita->puedeCancelarse(), $estado->value);
        }
    }

    public function test_una_cita_activa_se_puede_cancelar(): void
    {
        foreach (EstadoCita::activos() as $estado) {
            $this->assertTrue($this->cita($estado)->puedeCancelarse(), $estado->value);
        }
    }

    public function test_formatea_la_hora_y_el_inicio(): void
    {
        $cita = $this->cita();

        $this->assertSame('10:00', $cita->hora_corta);
        $this->assertSame('2026-10-12 10:00', $cita->inicio->format('Y-m-d H:i'));
    }

    public function test_estado_por_defecto_es_pendiente(): void
    {
        $this->assertSame(EstadoCita::Pendiente, (new Cita)->estado);
        $this->assertSame(0, (new Cita)->numero_reprogramaciones);
    }
}
