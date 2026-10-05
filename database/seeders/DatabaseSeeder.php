<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Los catalogos se cargan en todos los entornos. Los usuarios de prueba
     * (con contrasena conocida) nunca se crean en produccion: alli el primer
     * administrador se crea con "php artisan eirene:crear-admin".
     */
    public function run(): void
    {
        $this->call([
            EspecialidadSeeder::class,
            PromocionSeeder::class,
        ]);

        if (! app()->isProduction()) {
            $this->call(UsuariosDemoSeeder::class);
        }
    }
}
