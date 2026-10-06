<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Horario;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Calcula la disponibilidad real de un psicologo (RF-06 / RN-03): cruza sus
 * bloques de atencion semanales con las citas que ya ocupan ese dia.
 */
class AgendaService
{
    public const LIBRE = 'libre';         // Verde: se puede reservar

    public const OCUPADO = 'ocupado';     // Rojo: ya tiene una cita

    public const BLOQUEADO = 'bloqueado'; // Gris: bloque pausado u hora ya pasada

    /**
     * Todas las horas del dia con su estado, para la matriz de colores.
     *
     * @param  int|null  $ignorarCitaId  al reprogramar, la hora de la propia cita cuenta como libre
     * @return list<array{hora: string, estado: string}>
     */
    public function agendaDelDia(int $psicologoId, CarbonInterface|string $fecha, ?int $ignorarCitaId = null): array
    {
        $fecha = Carbon::parse($fecha)->startOfDay();

        if ($fecha->lt(today()) || ! $this->psicologoAtiende($psicologoId)) {
            return [];
        }

        $bloques = Horario::where('psicologo_id', $psicologoId)
            ->where('dia_semana', $fecha->dayOfWeek) // 0=domingo ... 6=sabado
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

        $agenda = [];

        foreach ($bloques as $bloque) {
            $inicio = $fecha->copy()->setTimeFromTimeString($bloque->hora_inicio);
            $fin = $fecha->copy()->setTimeFromTimeString($bloque->hora_fin);

            for (; $inicio->copy()->addMinutes($duracion)->lte($fin); $inicio->addMinutes($intervalo)) {
                $hora = $inicio->format('H:i');

                $estado = match (true) {
                    in_array($hora, $ocupadas, true) => self::OCUPADO,
                    ! $bloque->activo, $inicio->lt($limiteHoy) => self::BLOQUEADO,
                    default => self::LIBRE,
                };

                // Si dos bloques generan la misma hora, prevalece el estado mas restrictivo.
                $agenda[$hora] = isset($agenda[$hora]) && $agenda[$hora] !== self::LIBRE ? $agenda[$hora] : $estado;
            }
        }

        ksort($agenda);

        return array_map(
            fn (string $hora, string $estado) => ['hora' => $hora, 'estado' => $estado],
            array_keys($agenda),
            array_values($agenda),
        );
    }

    /**
     * Horas libres (formato H:i) del psicologo en la fecha indicada.
     *
     * @return list<string>
     */
    public function horasDisponibles(int $psicologoId, CarbonInterface|string $fecha, ?int $ignorarCitaId = null): array
    {
        return array_values(array_map(
            fn (array $franja) => $franja['hora'],
            array_filter(
                $this->agendaDelDia($psicologoId, $fecha, $ignorarCitaId),
                fn (array $franja) => $franja['estado'] === self::LIBRE,
            ),
        ));
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
