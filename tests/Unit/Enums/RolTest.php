<?php

namespace Tests\Unit\Enums;

use App\Enums\EstadoPago;
use App\Enums\MetodoPago;
use App\Enums\Rol;
use PHPUnit\Framework\TestCase;

class RolTest extends TestCase
{
    public function test_solo_administrador_y_recepcionista_son_personal_administrativo(): void
    {
        $this->assertTrue(Rol::Administrador->esPersonalAdministrativo());
        $this->assertTrue(Rol::Recepcionista->esPersonalAdministrativo());
        $this->assertFalse(Rol::Psicologo->esPersonalAdministrativo());
        $this->assertFalse(Rol::Paciente->esPersonalAdministrativo());
    }

    public function test_los_valores_coinciden_con_la_base_de_datos(): void
    {
        $this->assertSame(
            ['administrador', 'recepcionista', 'psicologo', 'paciente'],
            array_map(fn (Rol $rol) => $rol->value, Rol::cases()),
        );
        $this->assertSame(
            ['tarjeta', 'yape_plin', 'transferencia', 'efectivo'],
            array_map(fn (MetodoPago $m) => $m->value, MetodoPago::cases()),
        );
        $this->assertSame(
            ['pendiente', 'confirmado', 'rechazado'],
            array_map(fn (EstadoPago $e) => $e->value, EstadoPago::cases()),
        );
    }

    public function test_todas_las_opciones_tienen_etiqueta_legible(): void
    {
        foreach ([...Rol::cases(), ...MetodoPago::cases(), ...EstadoPago::cases()] as $caso) {
            $this->assertNotSame('', $caso->etiqueta());
        }

        $this->assertSame('Yape / Plin', MetodoPago::YapePlin->etiqueta());
    }
}
