<?php

namespace Tests\Fase8;

use App\Models\Cita;
use App\Models\HistorialClinico;
use App\Models\Paciente;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Process;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * Fase 8 - System Testing. Casos de seguridad verificables sin navegador
 * (CP-SYS-11, 13 a 23, 33 y 34). Los flujos E2E se ejecutan con Laravel Dusk
 * (tests/Browser) y el rendimiento con k6 (tests/Carga).
 */
class SeguridadDelSistemaTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_CP_SYS_11_psicologo_que_abre_admin_promociones_recibe_403(): void
    {
        $this->actingAs(User::factory()->psicologo()->create())
            ->get('/admin/promociones')
            ->assertForbidden()
            ->assertSee('Acceso no permitido');
    }

    public function test_CP_SYS_13_cookie_de_sesion_alterada_redirige_al_login(): void
    {
        $usuario = User::factory()->create(['email' => 'ana@eirene.test']);
        $this->post(route('login'), ['email' => 'ana@eirene.test', 'password' => UserFactory::CONTRASENA]);
        $this->assertAuthenticatedAs($usuario);

        // Una peticion nueva que llega con una cookie de sesion falsificada.
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        $this->withUnencryptedCookie(config('session.cookie'), str_repeat('A', 40))
            ->get(route('home'))
            ->assertRedirect(route('login'));
    }

    /**
     * No aplica: el sistema no usa JWT sino sesion con cookie cifrada. Se
     * comprueba que un token "Bearer" (incluso con alg "none") es ignorado.
     */
    public function test_CP_SYS_14_un_token_jwt_con_algoritmo_none_es_ignorado(): void
    {
        $cabecera = rtrim(strtr(base64_encode('{"alg":"none","typ":"JWT"}'), '+/', '-_'), '=');
        $carga = rtrim(strtr(base64_encode('{"sub":1,"role":"administrador"}'), '+/', '-_'), '=');
        User::factory()->administrador()->create();

        $this->withHeader('Authorization', "Bearer {$cabecera}.{$carga}.")
            ->get(route('admin.pacientes.index'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_CP_SYS_15_usuario_que_abre_la_cita_de_otro_recibe_403(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $ajena = $this->citaPara($psicologo);

        $this->actingAs($this->usuarioPaciente())->get("/citas/{$ajena->id}")->assertForbidden();
        $this->actingAs($this->usuarioPaciente())->get("/citas/{$ajena->id}/reprogramar")->assertForbidden();
        $this->actingAs($this->usuarioPaciente())->get("/citas/{$ajena->id}/cancelar")->assertForbidden();
        $this->actingAs($this->usuarioPaciente())->get('/citas/99999')->assertNotFound();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function cargasSql(): array
    {
        return [
            'tautologia' => ["' OR '1'='1"],
            'comentario' => ["1'; -- "],
            'union' => ["' UNION SELECT password FROM users --"],
            'drop' => ["'; DROP TABLE pacientes; --"],
        ];
    }

    #[DataProvider('cargasSql')]
    public function test_CP_SYS_16_inyeccion_sql_en_dni_y_busquedas_sin_fuga_ni_error_500(string $carga): void
    {
        $admin = User::factory()->administrador()->create();
        Paciente::factory()->create(['nombres' => 'Registro', 'apellidos' => 'Confidencial', 'dni' => '70000001']);

        $this->actingAs($admin)->post(route('admin.pacientes.store'), ['nombres' => 'X', 'apellidos' => 'Y', 'dni' => $carga])
            ->assertStatus(302)->assertSessionHasErrors('dni');

        foreach ([route('admin.pacientes.index', ['buscar' => $carga]), route('citas.index', ['buscar' => $carga])] as $url) {
            $this->actingAs($admin)->get($url)->assertOk()->assertDontSee('Confidencial')->assertDontSee('$2y$');
        }

        $this->assertTrue(DB::getSchemaBuilder()->hasTable('pacientes'));
        $this->assertSame(1, Paciente::count());
    }

    public function test_CP_SYS_17_xss_en_el_historial_clinico_se_escapa(): void
    {
        $historial = HistorialClinico::factory()->create(['notas_sesion' => '<script>alert("xss")</script> Paciente estable']);

        $this->actingAs($historial->psicologo)
            ->get(route('psicologo.historial.paciente', $historial->paciente))
            ->assertOk()
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertSee('&lt;script&gt;', false);

        // Ninguna vista imprime datos sin escapar con {!! !!}.
        $sinEscapar = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($archivo) => str_contains($archivo->getContents(), '{!!'))
            ->map(fn ($archivo) => $archivo->getRelativePathname())
            ->values()
            ->all();
        $this->assertSame([], $sinEscapar);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function archivosPeligrosos(): array
    {
        return [
            'script PHP' => ['voucher.php', 'application/x-httpd-php'],
            'ejecutable' => ['voucher.exe', 'application/x-msdownload'],
            'doble extension' => ['voucher.jpg.php', 'application/x-httpd-php'],
            'HTML con scripts' => ['voucher.html', 'text/html'],
        ];
    }

    /**
     * El contenido del archivo es inofensivo a proposito: la validacion
     * rechaza por tipo y extension, y un codigo malicioso real seria
     * eliminado por el antivirus del equipo de pruebas.
     */
    #[DataProvider('archivosPeligrosos')]
    public function test_CP_SYS_18_subir_php_o_exe_como_voucher_se_rechaza(string $nombre, string $mime): void
    {
        Storage::fake('local');
        [$psicologo] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $cita = $this->citaPara($psicologo, $this->fichaDe($usuario));

        $this->actingAs($usuario)
            ->post(route('pagos.store', $cita), [
                'monto' => 80, 'metodo_pago' => 'yape_plin',
                'comprobante' => UploadedFile::fake()->createWithContent($nombre, '<?php echo "archivo de prueba"; ?>')->mimeType($mime),
            ])
            ->assertSessionHasErrors('comprobante');

        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertSame(0, $cita->pagos()->count());
    }

    public function test_CP_SYS_19_cincuenta_intentos_fallidos_activan_el_bloqueo(): void
    {
        config(['eirene.limite_formularios_por_minuto' => 1000]); // aisla el bloqueo por usuario
        User::factory()->create(['email' => 'ana@eirene.test']);
        $bloqueado = 0;

        for ($i = 0; $i < 50; $i++) {
            $this->post(route('login'), ['email' => 'ana@eirene.test', 'password' => "Intento{$i}x"]);
            $bloqueado += str_contains((string) session('errors')?->first('email'), 'Demasiados intentos') ? 1 : 0;
        }

        $this->assertGreaterThanOrEqual(45, $bloqueado);

        // Con la contrasena correcta sigue bloqueado durante la ventana.
        $this->post(route('login'), ['email' => 'ana@eirene.test', 'password' => UserFactory::CONTRASENA]);
        $this->assertGuest();
    }

    public function test_CP_SYS_19b_el_limite_por_ip_devuelve_429(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('login'), ['email' => "u{$i}@eirene.test", 'password' => 'x']);
        }

        $this->post(route('login'), ['email' => 'u99@eirene.test', 'password' => 'x'])
            ->assertStatus(429)
            ->assertSee('Demasiadas solicitudes');
    }

    /**
     * En las pruebas Laravel desactiva el CSRF; aqui se vuelve a activar para
     * comprobar que un formulario sin token recibe 419.
     */
    public function test_CP_SYS_20_formulario_sin_token_csrf_recibe_419(): void
    {
        $this->app->singleton(ValidateCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends ValidateCsrfToken
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });

        $this->actingAs(User::factory()->administrador()->create())
            ->post(route('admin.promociones.store'), ['nombre' => 'Sin token', 'numero_sesiones' => 1, 'precio' => 10])
            ->assertStatus(419)
            ->assertSee('La sesión expiró');

        $this->assertDatabaseCount('promociones', 0);
    }

    public function test_CP_SYS_21_con_app_debug_false_un_error_no_muestra_detalles_internos(): void
    {
        config(['app.debug' => false]);
        Route::middleware('web')->get('/_falla', fn () => DB::select('select * from tabla_inexistente'));

        $respuesta = $this->get('/_falla');

        $respuesta->assertStatus(500)
            ->assertDontSee('tabla_inexistente')
            ->assertDontSee('SQLSTATE')
            ->assertDontSee(base_path())
            ->assertSee('Algo salió mal');
    }

    public function test_CP_SYS_22_en_la_tabla_users_solo_hay_hashes_bcrypt(): void
    {
        $this->seed();

        $contrasenas = DB::table('users')->pluck('password');

        $this->assertNotEmpty($contrasenas);
        foreach ($contrasenas as $hash) {
            $this->assertMatchesRegularExpression('/^\$2y\$\d{2}\$.{53}$/', $hash);
        }
        $this->assertFalse($contrasenas->contains('contraseña'));
    }

    public function test_CP_SYS_23_el_archivo_env_esta_fuera_del_repositorio(): void
    {
        $this->assertContains('.env', array_map('trim', file(base_path('.gitignore'))));

        $git = new Process(['git', 'ls-files', '--error-unmatch', '.env'], base_path());
        $git->run();
        $this->assertFalse($git->isSuccessful(), '.env no debe estar versionado en git.');
    }

    public function test_CP_SYS_33_laravel_actualizado_y_sin_vulnerabilidades_conocidas(): void
    {
        $this->assertTrue(version_compare(app()->version(), '12.0.0', '>='), 'Se esperaba Laravel 12 o superior.');

        $auditoria = Process::fromShellCommandline('composer audit --format=json --no-interaction', base_path());
        $auditoria->setTimeout(120)->run();
        $resultado = json_decode($auditoria->getOutput(), true);

        if (! is_array($resultado)) {
            $this->markTestSkipped('No se pudo ejecutar "composer audit" (sin red o sin Composer).');
        }

        $this->assertSame([], $resultado['advisories'] ?? [], 'composer audit encontro vulnerabilidades.');
    }

    public function test_CP_SYS_34_los_vouchers_no_se_pueden_ejecutar_desde_public(): void
    {
        Storage::fake('local');
        [$psicologo] = $this->psicologoConAgenda();
        $usuario = $this->usuarioPaciente();
        $cita = $this->citaPara($psicologo, $this->fichaDe($usuario));

        $this->actingAs($usuario)->post(route('pagos.store', $cita), [
            'monto' => 80, 'metodo_pago' => 'yape_plin',
            'comprobante' => UploadedFile::fake()->create('voucher.png', 100, 'image/png'),
        ])->assertSessionHasNoErrors();

        $pago = Cita::sole()->pagos()->sole();

        // Se guarda en el disco privado, con nombre aleatorio y fuera de /public.
        $this->assertStringStartsWith("comprobantes/{$cita->id}/", (string) $pago->comprobante_path);
        $this->assertStringNotContainsString('voucher', (string) $pago->comprobante_path);
        $this->assertSame(storage_path('app/private'), config('filesystems.disks.local.root'));
        $this->assertFileDoesNotExist(public_path('storage/'.$pago->comprobante_path));

        // Se sirve con cabeceras que impiden ejecutarlo y solo a quien puede ver la cita.
        $this->actingAs($usuario)->get(route('pagos.comprobante', $pago))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Security-Policy');
        $this->actingAs($this->usuarioPaciente())->get(route('pagos.comprobante', $pago))->assertForbidden();
    }
}
