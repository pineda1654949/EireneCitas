<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = ['psicologo_id', 'dia_semana', 'hora_inicio', 'hora_fin', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public const DIAS = [
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miercoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sabado',
    ];

    public function psicologo()
    {
        return $this->belongsTo(User::class, 'psicologo_id');
    }

    /**
     * Horas libres (formato H:i) de un psicologo en una fecha, cruzando su
     * horario configurado con las citas que ya ocupan ese dia (RF-02).
     * $ignorarCitaId permite excluir la propia cita al reprogramarla.
     */
    public static function horasDisponibles($psicologoId, $fecha, $ignorarCitaId = null): array
    {
        $fecha = Carbon::parse($fecha);

        $bloques = static::where('psicologo_id', $psicologoId)
            ->where('dia_semana', $fecha->dayOfWeek) // 0=domingo ... 6=sabado
            ->where('activo', true)
            ->orderBy('hora_inicio')
            ->get();

        $horasOcupadas = Cita::where('psicologo_id', $psicologoId)
            ->whereDate('fecha', $fecha->toDateString())
            ->whereIn('estado', Cita::ESTADOS_ACTIVOS)
            ->when($ignorarCitaId, fn ($q) => $q->where('id', '!=', $ignorarCitaId))
            ->pluck('hora')
            ->map(fn ($h) => substr($h, 0, 5))
            ->toArray();

        $disponibles = [];

        foreach ($bloques as $bloque) {
            $inicio = Carbon::parse($bloque->hora_inicio);
            $fin = Carbon::parse($bloque->hora_fin);

            // Sesiones de 50 minutos, con 10 minutos de margen entre citas.
            while ($inicio->copy()->addMinutes(50)->lte($fin)) {
                $horaTexto = $inicio->format('H:i');
                if (!in_array($horaTexto, $horasOcupadas)) {
                    $disponibles[] = $horaTexto;
                }
                $inicio->addMinutes(60);
            }
        }

        return $disponibles;
    }
}
