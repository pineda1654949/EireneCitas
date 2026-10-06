<?php

namespace Database\Seeders;

use App\Models\Promocion;
use Illuminate\Database\Seeder;

class PromocionSeeder extends Seeder
{
    public function run(): void
    {
        $promociones = [
            ['nombre' => 'Sesion individual', 'descripcion' => 'Una sesion de terapia psicologica.', 'numero_sesiones' => 1, 'precio' => 80.00],
            ['nombre' => 'Paquete 4 sesiones', 'descripcion' => 'Paquete mensual de 4 sesiones con 10% de descuento.', 'numero_sesiones' => 4, 'precio' => 288.00],
            ['nombre' => 'Paquete 8 sesiones', 'descripcion' => 'Paquete bimestral de 8 sesiones con 15% de descuento.', 'numero_sesiones' => 8, 'precio' => 544.00],
        ];

        foreach ($promociones as $promocion) {
            Promocion::firstOrCreate(['nombre' => $promocion['nombre']], $promocion + ['activa' => true]);
        }
    }
}
