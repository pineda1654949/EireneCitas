<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Punto de entrada unico tras el login. Segun el rol del usuario
     * (RF-07) se muestra el panel correspondiente con datos resumen.
     */
    public function index()
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'administrador':
                return view('dashboard.admin', [
                    'totalPacientes' => Paciente::count(),
                    'totalPsicologos' => User::psicologos()->count(),
                    'citasPendientes' => Cita::where('estado', 'pendiente')->count(),
                    'citasConfirmadas' => Cita::where('estado', 'confirmada')->count(),
                    'citasAtendidas' => Cita::where('estado', 'atendida')->count(),
                    'citasCanceladas' => Cita::where('estado', 'cancelada')->count(),
                    'ultimasCitas' => Cita::with(['paciente', 'psicologo'])->latest()->take(8)->get(),
                ]);

            case 'recepcionista':
                return view('dashboard.recepcionista', [
                    'citasHoy' => Cita::with(['paciente', 'psicologo'])
                        ->whereDate('fecha', now()->toDateString())
                        ->orderBy('hora')->get(),
                    'citasPendientes' => Cita::where('estado', 'pendiente')->count(),
                ]);

            case 'psicologo':
                return view('dashboard.psicologo', [
                    'citasHoy' => Cita::with('paciente')
                        ->where('psicologo_id', $user->id)
                        ->whereDate('fecha', now()->toDateString())
                        ->orderBy('hora')->get(),
                    'proximasCitas' => Cita::with('paciente')
                        ->where('psicologo_id', $user->id)
                        ->where('fecha', '>=', now()->toDateString())
                        ->whereIn('estado', Cita::ESTADOS_ACTIVOS)
                        ->orderBy('fecha')->orderBy('hora')->take(10)->get(),
                ]);

            default: // paciente
                $paciente = Paciente::where('user_id', $user->id)->first();
                return view('dashboard.paciente', [
                    'paciente' => $paciente,
                    'misCitas' => $paciente
                        ? Cita::with(['psicologo', 'especialidad'])
                            ->where('paciente_id', $paciente->id)
                            ->orderBy('fecha', 'desc')->take(10)->get()
                        : collect(),
                ]);
        }
    }
}
