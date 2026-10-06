<?php

namespace Tests\Fase6;

use App\Enums\EstadoAtencion;
use App\Enums\EstadoPago;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ValueError;

/**
 * Fase 6 - Unit Testing. Modelos Eloquent (CP-UT-10 a CP-UT-19).
 */
class ModelosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->administrador()->create();
    }

    public function test_CP_UT_10_paciente_con_dni_duplicado_lanza_excepcion_de_clave_unica(): void
    {
        Paciente::factory()->create(['dni' => '70000001']);

        $this->expectException(UniqueConstraintViolationException::class);
        Paciente::factory()->create(['dni' => '70000001']);
    }

    public function test_CP_UT_11_paciente_con_correo_duplicado_lanza_excepcion_de_clave_unica(): void
    {
        Paciente::factory()->create(['correo' => 'ana@correo.pe']);

        $this->expectException(UniqueConstraintViolationException::class);
        Paciente::factory()->create(['correo' => 'ana@correo.pe']);
    }

    public function test_CP_UT_12_paciente_sin_nombres_da_error_de_campo_obligatorio(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.pacientes.store'), ['nombres' => '', 'apellidos' => 'Rios'])
            ->assertSessionHasErrors(['nombres' => 'El campo nombres es obligatorio.']);

        $this->assertDatabaseCount('pacientes', 0);
    }

    public function test_CP_UT_13_edad_cero_o_negativa_da_error(): void
    {
        foreach ([0, -5] as $edad) {
            $this->actingAs($this->admin())
                ->post(route('admin.pacientes.store'), ['nombres' => 'Ana', 'apellidos' => 'Rios', 'edad' => $edad])
                ->assertSessionHasErrors('edad');
        }

        $this->assertDatabaseCount('pacientes', 0);
    }

    public function test_CP_UT_14_estados_por_defecto_inscripto_y_pago_pendiente(): void
    {
        $paciente = Paciente::create(['nombres' => 'Ana', 'apellidos' => 'Rios'])->fresh();
        $pago = Pago::create([
            'cita_id' => Cita::factory()->create()->id, 'monto' => 80, 'metodo_pago' => 'yape_plin',
        ])->fresh();

        $this->assertSame(EstadoAtencion::Inscripto, $paciente?->estado_atencion);
        $this->assertSame(EstadoPago::Pendiente, $pago?->estado);
    }

    public function test_CP_UT_15_contador_de_reprogramaciones_por_defecto_es_cero(): void
    {
        $cita = Cita::factory()->create();
        // Se crea sin indicar el contador: debe tomar el valor por defecto.
        $nueva = Cita::create([
            'paciente_id' => $cita->paciente_id, 'psicologo_id' => $cita->psicologo_id,
            'fecha' => '2026-10-19', 'hora' => '10:00',
        ])->fresh();

        $this->assertSame(0, $nueva?->numero_reprogramaciones);
    }

    public function test_CP_UT_16_monto_de_pago_se_guarda_con_dos_decimales(): void
    {
        $pago = Pago::factory()->create(['monto' => 80.5]);

        $this->assertSame('80.50', $pago->fresh()?->monto);
    }

    public function test_CP_UT_17_promocion_con_precio_menor_o_igual_a_cero_da_error(): void
    {
        foreach ([0, -10] as $precio) {
            $this->actingAs($this->admin())
                ->post(route('admin.promociones.store'), ['nombre' => 'Paquete', 'numero_sesiones' => 4, 'precio' => $precio])
                ->assertSessionHasErrors('precio');
        }

        $this->assertDatabaseCount('promociones', 0);
    }

    public function test_CP_UT_18_usuario_con_correo_o_usuario_duplicado_lanza_excepcion(): void
    {
        User::factory()->create(['email' => 'admin']);

        $this->expectException(UniqueConstraintViolationException::class);
        User::factory()->create(['email' => 'admin']);
    }

    /**
     * El sistema tiene 4 roles: administrador, recepcionista, psicologo y
     * paciente (este ultimo para el registro publico).
     */
    public function test_CP_UT_19_usuario_con_rol_no_permitido_da_error(): void
    {
        $this->expectException(ValueError::class);

        User::factory()->create(['role' => 'superusuario']);
    }
}
