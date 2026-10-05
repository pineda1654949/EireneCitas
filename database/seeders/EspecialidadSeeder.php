<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use Illuminate\Database\Seeder;

class EspecialidadSeeder extends Seeder
{
    /**
     * Especialidades / motivos de consulta para derivar al paciente al
     * psicologo adecuado (Cap. 2.2 y 3.4 del documento).
     */
    public const ESPECIALIDADES = [
        'Ansiedad y estres' => 'Manejo de ansiedad, estres laboral y ataques de panico.',
        'Depresion' => 'Acompanamiento en estados depresivos y duelo.',
        'Terapia de pareja' => 'Comunicacion, conflictos y acuerdos en la pareja.',
        'Terapia infantil' => 'Atencion de ninos y orientacion a padres.',
        'Autoestima y desarrollo personal' => 'Fortalecimiento de la autoestima y habilidades personales.',
    ];

    public function run(): void
    {
        foreach (self::ESPECIALIDADES as $nombre => $descripcion) {
            Especialidad::updateOrCreate(['nombre' => $nombre], ['descripcion' => $descripcion]);
        }
    }
}
