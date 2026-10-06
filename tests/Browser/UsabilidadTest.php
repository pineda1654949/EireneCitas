<?php

namespace Tests\Browser;

use App\Models\User;
use Facebook\WebDriver\WebDriverKeys;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Fase 8 - System Testing. Usabilidad, compatibilidad y accesibilidad
 * (CP-SYS-29 a CP-SYS-32).
 */
class UsabilidadTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function test_CP_SYS_29_vista_movil_con_menu_desplegable(): void
    {
        $recepcion = User::factory()->recepcionista()->create(['email' => 'recepcion@eirene.test']);

        $this->browse(function (Browser $browser) use ($recepcion) {
            $browser->resize(390, 844); // iPhone 12/13/14
            $this->iniciarSesion($browser, $recepcion->email);

            $oculto = fn () => $browser->script("return document.getElementById('sidebar').classList.contains('-translate-x-full');")[0];

            $this->assertTrue($oculto(), 'En movil el menu inicia oculto.');
            $browser->click('[data-sidebar-open]')->pause(300);
            $this->assertFalse($oculto(), 'El boton de menu lo despliega.');

            $browser->screenshot('CP-SYS-29-menu-movil')
                ->clickLink('Pacientes')
                ->waitForLocation('/admin/pacientes')
                ->assertSee('Pacientes');

            // Sin desplazamiento horizontal: el contenido cabe en el ancho del telefono.
            $this->assertFalse($browser->script('return document.documentElement.scrollWidth > window.innerWidth;')[0]);
        });
    }

    /**
     * Compatibilidad: se ejecuta en Google Chrome (motor Chromium, que tambien
     * usan Edge y Opera). Firefox y Safari quedan como verificacion manual.
     */
    public function test_CP_SYS_30_funciona_en_chrome(): void
    {
        $this->browse(function (Browser $browser) {
            $agente = $browser->visit('/login')->script('return navigator.userAgent;')[0];

            $this->assertStringContainsString('Chrome', $agente);
            $browser->assertSee('Inicia sesión');
        });
    }

    public function test_CP_SYS_31_los_mensajes_de_error_son_claros_y_visibles(): void
    {
        User::factory()->create(['email' => 'ana@correo.pe']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'ana@correo.pe')
                ->type('password', 'ClaveIncorrecta1')
                ->press('Ingresar')
                ->waitFor('[role=alert]')
                ->assertSeeIn('[role=alert]', 'Las credenciales no coinciden con nuestros registros.')
                ->assertAttribute('#email', 'aria-invalid', 'true')
                ->screenshot('CP-SYS-31-mensaje-de-error');
        });
    }

    public function test_CP_SYS_32_accesibilidad_basica(): void
    {
        $recepcion = User::factory()->recepcionista()->create(['email' => 'recepcion@eirene.test']);

        $this->browse(function (Browser $browser) use ($recepcion) {
            $this->iniciarSesion($browser, $recepcion->email)->visit('/admin/pacientes/create');

            $this->assertSame('es', $browser->script('return document.documentElement.lang;')[0]);
            $browser->assertPresent('a[href="#contenido"]')            // enlace "Saltar al contenido"
                ->assertPresent('nav a[aria-current="page"]');           // pagina actual anunciada

            // Todo campo visible tiene una etiqueta asociada.
            $sinEtiqueta = $browser->script(<<<'JS'
                return [...document.querySelectorAll('main input:not([type=hidden]), main select, main textarea')]
                    .filter(c => !c.labels?.length && !c.getAttribute('aria-label'))
                    .map(c => c.name);
            JS)[0];
            $this->assertSame([], $sinEtiqueta);

            // Navegacion con teclado: el primer Tab lleva al enlace de salto.
            $browser->visit('/admin/pacientes/create');
            $browser->driver->getKeyboard()->sendKeys(WebDriverKeys::TAB);
            $this->assertSame('#contenido', $browser->script('return document.activeElement.getAttribute("href");')[0]);
        });
    }
}
