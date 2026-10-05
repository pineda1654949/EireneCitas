<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\User;
use App\Services\AgendaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints AJAX para la consulta de disponibilidad en tiempo real (RF-02).
 */
class DisponibilidadController extends Controller
{
    /**
     * Psicologos activos que atienden una especialidad.
     */
    public function psicologosPorEspecialidad(Especialidad $especialidad): JsonResponse
    {
        $psicologos = $especialidad->psicologos()
            ->where('users.activo', true)
            ->orderBy('users.name')
            ->get(['users.id', 'users.name', 'users.apellidos'])
            ->map(fn (User $p) => ['id' => $p->id, 'nombre' => $p->nombre_completo]);

        return response()->json($psicologos);
    }

    /**
     * Matriz del dia con el estado de cada hora: libre (verde), ocupado
     * (rojo) o bloqueado (gris). La usa el formulario de reserva.
     */
    public function agendaDelDia(Request $request, AgendaService $agenda): JsonResponse
    {
        [$psicologoId, $fecha, $ignorar] = $this->parametros($request);

        return response()->json($agenda->agendaDelDia($psicologoId, $fecha, $ignorar));
    }

    /**
     * Horas libres de un psicologo en una fecha.
     */
    public function horasDisponibles(Request $request, AgendaService $agenda): JsonResponse
    {
        [$psicologoId, $fecha, $ignorar] = $this->parametros($request);

        return response()->json($agenda->horasDisponibles($psicologoId, $fecha, $ignorar));
    }

    /**
     * @return array{0: int, 1: string, 2: int|null}
     */
    private function parametros(Request $request): array
    {
        $datos = $request->validate([
            'psicologo_id' => ['required', 'integer', 'exists:users,id'],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'cita_id' => ['nullable', 'integer'],
        ]);

        // Solo se ignora la cita propia si el usuario realmente puede verla.
        $ignorar = null;
        if (! empty($datos['cita_id'])) {
            $cita = Cita::find($datos['cita_id']);
            $ignorar = $cita && $request->user()?->can('view', $cita) ? $cita->id : null;
        }

        return [(int) $datos['psicologo_id'], $datos['fecha'], $ignorar];
    }
}
