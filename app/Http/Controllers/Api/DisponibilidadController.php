<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Horario;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DisponibilidadController extends Controller
{
    /**
     * Devuelve los psicologos que atienden una especialidad dada.
     * RF-02 / derivacion segun sintomas (Cap. 3.4 del documento).
     */
    public function psicologosPorEspecialidad(Especialidad $especialidad)
    {
        return response()->json(
            $especialidad->psicologos()->select('users.id', 'users.name', 'users.apellidos')->get()
        );
    }

    /**
     * Devuelve las horas disponibles de un psicologo en una fecha dada,
     * cruzando su horario configurado con las citas ya registradas (RF-02).
     */
    public function horasDisponibles(Request $request)
    {
        $request->validate([
            'psicologo_id' => 'required|exists:users,id',
            'fecha' => 'required|date',
        ]);

        $fecha = Carbon::parse($request->fecha);
        $diaSemana = $fecha->dayOfWeek; // 0=domingo ... 6=sabado

        $bloques = Horario::where('psicologo_id', $request->psicologo_id)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->get();

        $horasOcupadas = Cita::where('psicologo_id', $request->psicologo_id)
            ->where('fecha', $fecha->toDateString())
            ->whereIn('estado', ['pendiente', 'confirmada'])
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

        return response()->json($disponibles);
    }
}
