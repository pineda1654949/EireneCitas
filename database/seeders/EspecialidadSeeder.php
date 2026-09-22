<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use App\Models\User;
use Illuminate\Database\Seeder;

class EspecialidadSeeder extends Seeder
{
    /**
     * Especialidades / motivos de consulta para derivar al paciente al
     * psicologo adecuado (Cap. 2.2 y 3.4 del documento).
     */
    public function run()
    {
        $nombres = [
            'Ansiedad y estres',
            'Depresion',
            'Terapia de pareja',
            'Terapia infantil',
            'Autoestima y desarrollo personal',
        ];

        $especialidades = collect($nombres)->map(function ($nombre) {
            return Especialidad::updateOrCreate(['nombre' => $nombre]);
        });

        $psico1 = User::where('email', 'psicologo1@eirene.test')->first();
        $psico2 = User::where('email', 'psicologo2@eirene.test')->first();

        if ($psico1) {
            $psico1->especialidades()->sync(
                $especialidades->whereIn('nombre', ['Ansiedad y estres', 'Depresion', 'Autoestima y desarrollo personal'])->pluck('id')
            );
        }

        if ($psico2) {
            $psico2->especialidades()->sync(
                $especialidades->whereIn('nombre', ['Terapia de pareja', 'Terapia infantil'])->pluck('id')
            );
        }
    }
}
