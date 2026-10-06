<?php

namespace Tests\Fase6;

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreaEscenarios;
use Tests\TestCase;

/**
 * Fase 6 - Unit Testing. Validaciones con FormRequest (CP-UT-20 a CP-UT-30).
 */
class ValidacionesTest extends TestCase
{
    use CreaEscenarios, RefreshDatabase;

    private User $recepcion;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('local');
        $this->recepcion = User::factory()->recepcionista()->create();
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function registrarPaciente(array $datos): TestResponse
    {
        return $this->actingAs($this->recepcion)
            ->post(route('admin.pacientes.store'), ['nombres' => 'Ana', 'apellidos' => 'Rios', ...$datos]);
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function casosDeDni(): array
    {
        return [
            'CP-UT-20 DNI de 7 digitos' => ['CP-UT-20', '7000001', false],
            'CP-UT-21 DNI de 8 digitos' => ['CP-UT-21', '70000001', true],
            'CP-UT-22 DNI con letras' => ['CP-UT-22', '7000A001', false],
        ];
    }

    #[DataProvider('casosDeDni')]
    public function test_CP_UT_20_21_22_validacion_del_dni(string $id, string $dni, bool $aceptado): void
    {
        $respuesta = $this->registrarPaciente(['dni' => $dni]);

        $aceptado ? $respuesta->assertSessionHasNoErrors() : $respuesta->assertSessionHasErrors('dni');
        $this->assertSame($aceptado ? 1 : 0, Paciente::count(), $id);
    }

    public function test_CP_UT_23_correo_mal_formado_se_rechaza(): void
    {
        $this->registrarPaciente(['correo' => 'ana.correo.pe'])
            ->assertSessionHasErrors(['correo' => 'El campo correo electrónico debe ser un correo electrónico válido.']);
    }

    public function test_CP_UT_24_correo_en_mayusculas_se_guarda_en_minusculas(): void
    {
        $this->registrarPaciente(['correo' => 'Ana.Rios@Correo.PE'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pacientes', ['correo' => 'ana.rios@correo.pe']);
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function casosDeCelular(): array
    {
        return [
            'CP-UT-25 no empieza con 9' => ['CP-UT-25', '887654321', false],
            'CP-UT-25 con 8 digitos' => ['CP-UT-25', '98765432', false],
            'CP-UT-25 con letras' => ['CP-UT-25', '98765432a', false],
            'CP-UT-26 celular valido' => ['CP-UT-26', '987654321', true],
        ];
    }

    #[DataProvider('casosDeCelular')]
    public function test_CP_UT_25_26_validacion_del_celular(string $id, string $celular, bool $aceptado): void
    {
        $respuesta = $this->registrarPaciente(['telefono' => $celular]);

        $aceptado
            ? $respuesta->assertSessionHasNoErrors()
            : $respuesta->assertSessionHasErrors(['telefono' => 'El teléfono debe tener 9 dígitos y empezar con 9 (por ejemplo 987654321).']);
    }

    /**
     * @return array<string, array{string, \Closure(): UploadedFile, bool}>
     */
    public static function casosDeVoucher(): array
    {
        return [
            'CP-UT-27 voucher .exe' => ['CP-UT-27', fn () => UploadedFile::fake()->create('voucher.exe', 50, 'application/x-msdownload'), false],
            'CP-UT-28 .jpg de mas de 5 MB' => ['CP-UT-28', fn () => UploadedFile::fake()->create('voucher.jpg', 5121, 'image/jpeg'), false],
            'CP-UT-29 .png de 1 MB' => ['CP-UT-29', fn () => UploadedFile::fake()->create('voucher.png', 1024, 'image/png'), true],
        ];
    }

    /**
     * @param  \Closure(): UploadedFile  $archivo
     */
    #[DataProvider('casosDeVoucher')]
    public function test_CP_UT_27_28_29_validacion_del_voucher(string $id, \Closure $archivo, bool $aceptado): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo);

        $respuesta = $this->actingAs($this->recepcion)->post(route('pagos.store', $cita), [
            'monto' => 80, 'metodo_pago' => 'yape_plin', 'comprobante' => $archivo(),
        ]);

        $aceptado ? $respuesta->assertSessionHasNoErrors() : $respuesta->assertSessionHasErrors('comprobante');
        $this->assertSame($aceptado ? 1 : 0, $cita->pagos()->count(), $id);
        $this->assertCount($aceptado ? 1 : 0, Storage::disk('local')->allFiles('comprobantes'), $id);
    }

    public function test_CP_UT_30_nota_de_historial_muy_corta_se_rechaza(): void
    {
        [$psicologo] = $this->psicologoConAgenda();
        $cita = $this->citaPara($psicologo, atributos: ['fecha' => now()->toDateString(), 'hora' => '09:00']);

        $this->actingAs($psicologo)
            ->post(route('psicologo.historial.store', $cita), ['notas_sesion' => 'Bien'])
            ->assertSessionHasErrors(['notas_sesion' => 'El campo notas de la sesión debe tener al menos 10 caracteres.']);

        $this->assertDatabaseCount('historiales_clinicos', 0);
    }
}
