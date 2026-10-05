<?php

namespace Tests\Feature\Seguridad;

use App\Models\HistorialClinico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Requisitos de seguridad para produccion (OWASP): cabeceras HTTP,
 * cifrado de datos clinicos y no exposicion de informacion sensible.
 */
class CabecerasYProteccionesTest extends TestCase
{
    use RefreshDatabase;

    public function test_las_respuestas_incluyen_cabeceras_de_seguridad(): void
    {
        $respuesta = $this->get(route('login'));

        $respuesta->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy');

        $csp = (string) $respuesta->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringNotContainsString('unsafe-eval', $csp);
    }

    public function test_hsts_solo_se_envia_por_https(): void
    {
        $this->get(route('login'))->assertHeaderMissing('Strict-Transport-Security');

        $this->get('https://localhost/login')->assertHeader('Strict-Transport-Security');
    }

    public function test_las_notas_clinicas_se_guardan_cifradas(): void
    {
        $historial = HistorialClinico::factory()->create(['notas_sesion' => 'Paciente refiere insomnio']);

        $valorEnBd = DB::table('historiales_clinicos')->where('id', $historial->id)->value('notas_sesion');

        $this->assertNotSame('Paciente refiere insomnio', $valorEnBd);
        $this->assertStringNotContainsString('insomnio', (string) $valorEnBd);
        $this->assertSame('Paciente refiere insomnio', $historial->fresh()?->notas_sesion);
    }

    public function test_las_contrasenas_se_guardan_con_hash(): void
    {
        $usuario = User::factory()->create(['password' => 'MiClave123']);

        $hash = DB::table('users')->where('id', $usuario->id)->value('password');

        $this->assertNotSame('MiClave123', $hash);
        $this->assertTrue(password_verify('MiClave123', (string) $hash));
        $this->assertArrayNotHasKey('password', $usuario->toArray());
    }

    public function test_el_endpoint_de_salud_responde(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_las_paginas_de_error_estan_en_espanol(): void
    {
        $this->get('/no-existe')->assertNotFound()->assertSee('Página no encontrada');
    }

    public function test_robots_txt_impide_la_indexacion(): void
    {
        $this->assertStringContainsString('Disallow: /', (string) file_get_contents(public_path('robots.txt')));
    }
}
