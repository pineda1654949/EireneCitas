<?php

namespace Tests;

use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

/**
 * Base de las pruebas E2E de la Fase 8 (System Testing) con Laravel Dusk.
 * El sistema corre en http://127.0.0.1:8010 contra la base eirene_dusk
 * (ver .env.dusk.local y docs/plan-pruebas/08_Fase8_System_Testing.md).
 */
abstract class DuskTestCase extends BaseTestCase
{
    /** Contrasena de los usuarios creados con factories (UserFactory::CONTRASENA). */
    protected const CLAVE = 'Secreta123';

    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            '--window-size=1440,900',
            '--lang=es-PE',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $options)
        );
    }

    /**
     * Dusk reutiliza el navegador entre pruebas: cada prueba empieza sin
     * sesion abierta y con el tamano de escritorio.
     */
    protected function setUp(): void
    {
        parent::setUp();

        foreach (static::$browsers ?? [] as $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->resize(1440, 900);
        }
    }

    // ---------------------------------------------------------------- helpers

    protected function iniciarSesion(Browser $browser, string $usuario, string $clave = self::CLAVE): Browser
    {
        return $browser->visit('/login')
            ->type('email', $usuario)
            ->type('password', $clave)
            ->press('Ingresar')
            ->waitForLocation('/home');
    }

    protected function cerrarSesion(Browser $browser): Browser
    {
        return $browser->visit('/home')
            ->click('[data-dropdown-toggle]')
            ->waitForText('Cerrar sesión')
            ->press('Cerrar sesión')
            ->waitForLocation('/login');
    }

    /**
     * Los <input type="date"> dependen del idioma del navegador; se asigna el
     * valor ISO y se dispara "change" como lo haria el usuario.
     */
    protected function elegirFecha(Browser $browser, string $fecha): Browser
    {
        $browser->script(
            "const f = document.querySelector('[data-fecha]'); f.value = '{$fecha}'; f.dispatchEvent(new Event('change', {bubbles: true}));"
        );

        return $browser;
    }

    /**
     * Hace clic en una hora LIBRE (verde) de la matriz de la agenda.
     */
    protected function elegirHora(Browser $browser, string $hora): Browser
    {
        $xpath = "//label[contains(@class,'hora-libre')][normalize-space()='{$hora}']";

        return $browser->waitUsing(10, 100, fn () => count($browser->driver->findElements(
            WebDriverBy::xpath($xpath)
        )) > 0, "La hora {$hora} no aparece como libre.")
            ->clickAtXPath($xpath);
    }

    protected function proximoLunes(int $semanasDespues = 0): string
    {
        return Carbon::now()->next(Carbon::MONDAY)->addWeeks($semanasDespues)->toDateString();
    }

    /**
     * Psicologo activo con especialidad y bloque de lunes 09:00-13:00.
     *
     * @return array{0: User, 1: Especialidad}
     */
    protected function psicologoConAgenda(string $nombre = 'Maria', string $especialidad = 'Ansiedad y estres'): array
    {
        $psicologo = User::factory()->psicologo()->create([
            'name' => $nombre, 'apellidos' => 'Fernandez', 'email' => mb_strtolower($nombre).'@eirene.test',
        ]);
        $area = Especialidad::firstOrCreate(['nombre' => $especialidad]);
        $psicologo->especialidades()->attach($area);

        Horario::factory()->create([
            'psicologo_id' => $psicologo->id, 'dia_semana' => Carbon::MONDAY,
            'hora_inicio' => '09:00', 'hora_fin' => '13:00',
        ]);

        return [$psicologo, $area];
    }
}
