<?php

namespace Tests\Unit\Enums;

use App\Enums\EstadoCita;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EstadoCitaTest extends TestCase
{
    public function test_los_estados_activos_son_los_que_ocupan_horario(): void
    {
        $this->assertSame(
            [EstadoCita::Pendiente, EstadoCita::Confirmada, EstadoCita::Reprogramada],
            EstadoCita::activos(),
        );
        $this->assertSame(['pendiente', 'confirmada', 'reprogramada'], EstadoCita::valoresActivos());
    }

    /**
     * @return array<string, array{EstadoCita, bool, bool, bool}>
     */
    public static function reglasPorEstado(): array
    {
        // estado, estaActiva, estaCerrada, admiteConfirmacion
        return [
            'pendiente' => [EstadoCita::Pendiente, true, false, true],
            'confirmada' => [EstadoCita::Confirmada, true, false, false],
            'reprogramada' => [EstadoCita::Reprogramada, true, false, true],
            'atendida' => [EstadoCita::Atendida, false, true, false],
            'cancelada' => [EstadoCita::Cancelada, false, true, false],
        ];
    }

    #[DataProvider('reglasPorEstado')]
    public function test_reglas_de_cada_estado(EstadoCita $estado, bool $activa, bool $cerrada, bool $confirmable): void
    {
        $this->assertSame($activa, $estado->estaActiva());
        $this->assertSame($cerrada, $estado->estaCerrada());
        $this->assertSame($confirmable, $estado->admiteConfirmacion());
    }

    public function test_cada_estado_tiene_etiqueta_y_tono_distintos(): void
    {
        $etiquetas = array_map(fn (EstadoCita $e) => $e->etiqueta(), EstadoCita::cases());
        $tonos = array_map(fn (EstadoCita $e) => $e->tono(), EstadoCita::cases());

        $this->assertCount(count(EstadoCita::cases()), array_unique($etiquetas));
        $this->assertCount(count(EstadoCita::cases()), array_unique($tonos));
    }
}
