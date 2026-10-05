<?php

namespace Tests\Feature\Database;

use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\Promocion;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\EspecialidadSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_en_desarrollo_carga_catalogos_y_usuarios_de_prueba(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(count(EspecialidadSeeder::ESPECIALIDADES), Especialidad::count());
        $this->assertSame(3, Promocion::count());

        $admin = User::where('email', 'admin')->sole();
        $this->assertTrue($admin->esAdministrador());
        $this->assertTrue(Hash::check('contraseña', $admin->password));
        $this->assertNotNull(User::where('email', 'paciente@eirene.test')->sole()->pacienteFicha);
    }

    public function test_en_produccion_no_crea_usuarios_con_contrasena_conocida(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        // Se invoca el seeder directamente: "db:seed" pide confirmacion en produccion.
        $this->app->make(DatabaseSeeder::class)->setContainer($this->app)->__invoke();

        $this->assertSame(0, User::count());
        $this->assertGreaterThan(0, Especialidad::count());
    }

    public function test_los_seeders_son_idempotentes(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, User::count());
        $this->assertSame(3, Promocion::count());
        $this->assertSame(20, Horario::count()); // 2 psicologos x 5 dias x 2 bloques (DEF-016)
    }
}
