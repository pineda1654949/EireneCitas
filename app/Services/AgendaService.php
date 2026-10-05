<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Horario;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Calcula la disponibilidad real de un psicologo (RF-02): cruza sus bloques
 * de atencion semanales con las citas que ya ocupan ese dia.
 */
class AgendaService
{
    /**
     * Horas libres (formato H:i) del psicologo en la fecha indicada.
     *
     * @param  int|null  $ignorarCitaId  al reprogramar, la hora de la propia cita cuenta como libre
     * @return list<string>
     */
    public function horasDisponibles(int $psicologoId, CarbonInterface|string $fecha, ?int $ignorarCitaId = null): array
    {
        $fecha = Carbon::parse($fecha)->startOfDay();

        if ($fecha->lt(today()) || ! $this->psicologoAtiende($psicologoId)) {
            return [];
        }

        $bloques = Horario::where('psicologo_id', $psicologoId)
            ->where('dia_semana', $fecha->dayOfWeek)
            ->where('activo', true)
            ->orderBy('hora_inicio')
            ->get();

        $ocupadas = Cita::activas()
            ->where('psicologo_id', $psicologoId)
            ->whereDate('fecha', $fecha->toDateString())
            ->when($ignorarCitaId, fn ($q) => $q->whereKeyNot($ignorarCitaId))
            ->pluck('hora')
            ->map(fn (string $hora) => substr($hora, 0, 5))
            ->all();

        $duracion = (int) config('eirene.citas.duracion_minutos');
        $intervalo = (int) config('eirene.citas.intervalo_minutos');
        $limiteHoy = now()->addMinutes((int) config('eirene.citas.anticipacion_minima_minutos'));

        $disponibles = [];

        foreach ($bloques as $bloque) {
            $inicio = $fecha->copy()->setTimeFromTimeString($bloque->hora_inicio);
            $fin = $fecha->copy()->setTimeFromTimeString($bloque->hora_fin);

            for (; $inicio->copy()->addMinutes($duracion)->lte($fin); $inicio->addMinutes($intervalo)) {
                $hora = $inicio->format('H:i');

                if (in_array($hora, $ocupadas, true) || $inicio->lt($limiteHoy)) {
                    continue;
                }

                $disponibles[] = $hora;
            }
        }

        return array_values(array_unique($disponibles));
    }

    public function estaDisponible(int $psicologoId, CarbonInterface|string $fecha, string $hora, ?int $ignorarCitaId = null): bool
    {
        return in_array(substr($hora, 0, 5), $this->horasDisponibles($psicologoId, $fecha, $ignorarCitaId), true);
    }

    private function psicologoAtiende(int $psicologoId): bool
    {
        return User::psicologos()->activos()->whereKey($psicologoId)->exists();
    }
}
