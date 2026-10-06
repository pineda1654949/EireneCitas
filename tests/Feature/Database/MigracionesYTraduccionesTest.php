<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Pruebas de proteccion surgidas de la revision extensiva:
 * DEF-007 (migraciones no reversibles) y DEF-009 (mensajes en ingles).
 */
class MigracionesYTraduccionesTest extends TestCase
{
    use RefreshDatabase;

    public function test_todas_las_migraciones_se_pueden_revertir_y_volver_a_aplicar(): void
    {
        $this->artisan('migrate:reset')->assertSuccessful();
        $this->assertFalse(Schema::hasTable('citas'));

        $this->artisan('migrate')->assertSuccessful();
        $this->assertTrue(Schema::hasTable('citas'));
        $this->assertTrue(Schema::hasTable('auditorias'));
    }

    public function test_todas_las_reglas_de_validacion_tienen_mensaje_en_espanol(): void
    {
        $ingles = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');
        $espanol = require lang_path('es/validation.php');

        $faltantes = array_diff($this->claves($ingles), $this->claves($espanol));

        $this->assertSame([], array_values($faltantes), 'Reglas sin traducir: '.implode(', ', $faltantes));
    }

    public function test_los_textos_del_framework_estan_traducidos(): void
    {
        $this->assertSame('No tienes permisos para realizar esta acción.', __('This action is unauthorized.'));
        $this->assertSame('Siguiente »', __('Next »'));
        $this->assertSame('El enlace para restablecer la contraseña no es válido o ya venció.', __('passwords.token'));
    }

    /**
     * @param  array<string, mixed>  $traducciones
     * @return list<string>
     */
    private function claves(array $traducciones, string $prefijo = ''): array
    {
        $claves = [];

        foreach ($traducciones as $clave => $valor) {
            if (in_array($clave, ['custom', 'attributes'], true)) {
                continue;
            }

            $claves = is_array($valor)
                ? [...$claves, ...$this->claves($valor, $prefijo.$clave.'.')]
                : [...$claves, $prefijo.$clave];
        }

        return $claves;
    }
}
