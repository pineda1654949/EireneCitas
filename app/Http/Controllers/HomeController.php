<?php

namespace App\Http\Controllers;

use App\Enums\EstadoCita;
use App\Enums\EstadoDerivacion;
use App\Enums\EstadoPago;
use App\Enums\Rol;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Panel principal: muestra un resumen distinto segun el rol (RF-07).
 */
class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var User $usuario */
        $usuario = $request->user();

        return match ($usuario->role) {
            Rol::Administrador => $this->panelAdministrador(),
            Rol::Recepcionista => $this->panelRecepcionista(),
            Rol::Psicologo => $this->panelPsicologo($usuario),
            Rol::Paciente => $this->panelPaciente($usuario),
        };
    }

    private function panelAdministrador(): View
    {
        $porEstado = Cita::query()
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('dashboard.admin', [
            'totalPacientes' => Paciente::count(),
            'totalPsicologos' => User::psicologos()->activos()->count(),
            'citasPorEstado' => $porEstado,
            'ingresosMes' => Pago::where('estado', EstadoPago::Confirmado->value)
                ->whereBetween('fecha_pago', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('monto'),
            'ultimasCitas' => Cita::with(['paciente', 'psicologo'])->latest()->take(8)->get(),
        ]);
    }

    private function panelRecepcionista(): View
    {
        return view('dashboard.recepcionista', [
            'citasHoy' => Cita::with(['paciente', 'psicologo'])
                ->whereDate('fecha', today())
                ->orderBy('hora')
                ->get(),
            'citasPendientes' => Cita::where('estado', EstadoCita::Pendiente->value)->count(),
            'pagosPorValidar' => Pago::where('estado', EstadoPago::Pendiente->value)->count(),
        ]);
    }

    private function panelPsicologo(User $psicologo): View
    {
        return view('dashboard.psicologo', [
            'citasHoy' => Cita::with('paciente')
                ->where('psicologo_id', $psicologo->id)
                ->whereDate('fecha', today())
                ->activas()
                ->orderBy('hora')
                ->get(),
            'proximasCitas' => Cita::with('paciente')
                ->where('psicologo_id', $psicologo->id)
                ->whereDate('fecha', '>', today())
                ->activas()
                ->orderBy('fecha')->orderBy('hora')
                ->take(10)
                ->get(),
            'derivacionesPendientes' => $psicologo->derivacionesRecibidas()
                ->where('estado', EstadoDerivacion::Pendiente->value)
                ->count(),
            'atendidasMes' => Cita::where('psicologo_id', $psicologo->id)
                ->where('estado', EstadoCita::Atendida->value)
                ->whereBetween('fecha', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
        ]);
    }

    private function panelPaciente(User $usuario): View
    {
        $paciente = $usuario->pacienteFicha;

        $citas = $paciente
            ? Cita::with(['psicologo', 'especialidad'])
                ->where('paciente_id', $paciente->id)
                ->orderByDesc('fecha')->orderByDesc('hora')
                ->take(10)
                ->get()
            : collect();

        // Consulta propia: la lista anterior solo trae las 10 citas mas
        // lejanas y podia omitir la mas cercana (DEF-015).
        $proximaCita = $paciente
            ? Cita::with(['psicologo', 'especialidad'])
                ->where('paciente_id', $paciente->id)
                ->activas()
                ->whereDate('fecha', '>=', today())
                ->orderBy('fecha')->orderBy('hora')
                ->get()
                ->first(fn (Cita $cita) => $cita->inicio->isFuture())
            : null;

        return view('dashboard.paciente', [
            'paciente' => $paciente,
            'misCitas' => $citas,
            'proximaCita' => $proximaCita,
        ]);
    }
}
