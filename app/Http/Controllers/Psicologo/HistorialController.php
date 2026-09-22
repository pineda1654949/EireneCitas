<?php

namespace App\Http\Controllers\Psicologo;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\HistorialClinico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * RF-06: Consulta de historia clinica.
 * Actividad TO-BE 15: "Atender sesion y registrar historial clinico" (Psicologo).
 */
class HistorialController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:psicologo']);
    }

    /**
     * Historia clinica completa de un paciente (todas sus sesiones con este
     * psicologo u otros, segun corresponda al flujo clinico).
     */
    public function porPaciente($pacienteId)
    {
        $historiales = HistorialClinico::with(['cita', 'psicologo'])
            ->where('paciente_id', $pacienteId)
            ->latest()
            ->get();

        return view('psicologo.historial.index', compact('historiales', 'pacienteId'));
    }

    public function create(Cita $cita)
    {
        if ($cita->psicologo_id !== Auth::id()) {
            abort(403);
        }

        return view('psicologo.historial.create', compact('cita'));
    }

    public function store(Request $request, Cita $cita)
    {
        if ($cita->psicologo_id !== Auth::id()) {
            abort(403);
        }

        $datos = $request->validate([
            'notas_sesion' => 'required|string',
            'avance' => 'nullable|string|max:255',
        ]);

        HistorialClinico::updateOrCreate(
            ['cita_id' => $cita->id],
            [
                'paciente_id' => $cita->paciente_id,
                'psicologo_id' => Auth::id(),
                'notas_sesion' => $datos['notas_sesion'],
                'avance' => $datos['avance'] ?? null,
            ]
        );

        $cita->update(['estado' => 'atendida']);

        return redirect()->route('citas.show', $cita)->with('status', 'Historial clinico registrado. Sesion marcada como atendida.');
    }
}
