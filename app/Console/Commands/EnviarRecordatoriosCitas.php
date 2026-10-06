<?php

namespace App\Console\Commands;

use App\Models\Cita;
use App\Notifications\RecordatorioDeCita;
use Illuminate\Console\Command;

/**
 * Envia un recordatorio por correo de las citas activas del dia siguiente.
 * Se ejecuta automaticamente cada dia (ver routes/console.php).
 */
class EnviarRecordatoriosCitas extends Command
{
    protected $signature = 'citas:enviar-recordatorios';

    protected $description = 'Envía recordatorios por correo de las citas de mañana';

    public function handle(): int
    {
        $enviados = 0;

        Cita::with(['paciente.user', 'psicologo', 'especialidad'])
            ->activas()
            ->whereDate('fecha', today()->addDay())
            ->chunkById(100, function ($citas) use (&$enviados) {
                foreach ($citas as $cita) {
                    $cita->paciente->notify(new RecordatorioDeCita($cita));
                    $enviados++;
                }
            });

        $this->info("Recordatorios enviados: {$enviados}");

        return self::SUCCESS;
    }
}
