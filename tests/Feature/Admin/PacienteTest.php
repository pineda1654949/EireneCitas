<?php

namespace Tests\Feature\Admin;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RF-05: registro y actualizacion de datos del paciente.
 */
class PacienteTest extends TestCase
{
    use RefreshDatabase;

    private User $recepcion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recepcion = User::factory()->recepcionista()->create();
    }

    public function test_registra_un_paciente(): void
    {
        $this->actingAs($this->recepcion)
            ->post(route('admin.pacientes.store'), [
                'nombres' => 'Jorge',
                'apellidos' => 'Salas',
                'dni' => '45678912',
                'edad' => 35,
                'correo' => 'jorge@correo.pe',
                'telefono' => '912345678',
            ])
            ->assertRedirect(route('admin.pacientes.index'));

        $this->assertDatabaseHas('pacientes', ['dni' => '45678912', 'nombres' => 'Jorge', 'user_id' => null]);
    }

    public function test_actualiza_los_datos_de_un_paciente(): void
    {
        $paciente = Paciente::factory()->create(['dni' => '45678912']);

        $this->actingAs($this->recepcion)->get(route('admin.pacientes.edit', $paciente))->assertOk()->assertSee($paciente->nombres);

        $this->actingAs($this->recepcion)
            ->put(route('admin.pacientes.update', $paciente), [
                'nombres' => 'Jorge Luis',
                'apellidos' => $paciente->apellidos,
                'dni' => '45678912', // el propio DNI no cuenta como duplicado
                'telefono' => '999888777',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Jorge Luis', $paciente->refresh()->nombres);
        $this->assertSame('999888777', $paciente->telefono);
    }

    public function test_no_permite_dni_duplicado_ni_edad_fuera_de_rango(): void
    {
        Paciente::factory()->create(['dni' => '45678912']);

        $this->actingAs($this->recepcion)
            ->post(route('admin.pacientes.store'), ['nombres' => 'A', 'apellidos' => 'B', 'dni' => '45678912', 'edad' => 150])
            ->assertSessionHasErrors(['dni', 'edad']);
    }

    public function test_busca_por_nombre_y_dni(): void
    {
        Paciente::factory()->create(['nombres' => 'Rosa', 'apellidos' => 'Huaman', 'dni' => '11112222']);
        Paciente::factory()->create(['nombres' => 'Pedro', 'apellidos' => 'Castillo', 'dni' => '33334444']);

        $this->actingAs($this->recepcion)->get(route('admin.pacientes.index', ['buscar' => 'Huaman']))
            ->assertSee('Rosa')->assertDontSee('Pedro');

        $this->actingAs($this->recepcion)->get(route('admin.pacientes.index', ['buscar' => '3333']))
            ->assertSee('Pedro')->assertDontSee('Rosa');
    }

    public function test_elimina_un_paciente_sin_citas(): void
    {
        $paciente = Paciente::factory()->create();

        $this->actingAs($this->recepcion)->delete(route('admin.pacientes.destroy', $paciente))->assertSessionHasNoErrors();

        $this->assertModelMissing($paciente);
    }

    public function test_no_elimina_un_paciente_con_citas(): void
    {
        $cita = Cita::factory()->create();

        $this->actingAs($this->recepcion)
            ->delete(route('admin.pacientes.destroy', $cita->paciente))
            ->assertSessionHasErrors('paciente');

        $this->assertModelExists($cita->paciente);
    }
}
