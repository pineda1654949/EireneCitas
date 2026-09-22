<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta todos los seeders del sistema Eirene.
     * Orden: usuarios base -> especialidades -> horarios -> promociones.
     */
    public function run()
    {
        $this->call([
            UsuariosSeeder::class,
            EspecialidadSeeder::class,
            HorarioSeeder::class,
            PromocionSeeder::class,
        ]);
    }
}
