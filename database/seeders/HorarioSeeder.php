<?php

namespace Database\Seeders;

use App\Models\Horario;
use App\Models\User;
use Illuminate\Database\Seeder;

class HorarioSeeder extends Seeder
{
    /**
     * Disponibilidad horaria de ejemplo (RF-02), para poder probar el
     * flujo de reserva de citas desde el primer arranque del sistema.
     */
    public function run()
    {
        $psico1 = User::where('email', 'psicologo1@eirene.test')->first();
        $psico2 = User::where('email', 'psicologo2@eirene.test')->first();

        // Lunes(1) a Viernes(5), 09:00 - 13:00 y 15:00 - 18:00
        foreach ([$psico1, $psico2] as $psicologo) {
            if (!$psicologo) {
                continue;
            }

            foreach (range(1, 5) as $dia) {
                Horario::updateOrCreate([
                    'psicologo_id' => $psicologo->id,
                    'dia_semana' => $dia,
                    'hora_inicio' => '09:00',
                    'hora_fin' => '13:00',
                ]);

                Horario::updateOrCreate([
                    'psicologo_id' => $psicologo->id,
                    'dia_semana' => $dia,
                    'hora_inicio' => '15:00',
                    'hora_fin' => '18:00',
                ]);
            }
        }
    }
}
