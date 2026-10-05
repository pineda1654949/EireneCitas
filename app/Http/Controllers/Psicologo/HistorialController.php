<?php

namespace App\Http\Controllers\Psicologo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Psicologo\HistorialClinicoRequest;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use App\Services\CitaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * RF-06: historia clinica.
 * Actividad TO-BE 15: "Atender sesion y registrar historial clinico".
 */
class HistorialController extends Controller
{
    public function __construct(private readonly CitaService $citas) {}

    /**
     * Historia clinica completa del paciente (todas sus sesiones atendidas).
     */
    public function porPaciente(Paciente $paciente): View
    {
        $this->authorize('verHistorial', $paciente);

        $historiales = $paciente->historialesClinicos()
            ->with(['cita', 'psicologo'])
            ->latest()
            ->get();

        return view('psicologo.historial.index', compact('historiales', 'paciente'));
    }

    public function create(Cita $cita): View
    {
        $this->authorize('atender', $cita);

        $cita->load(['paciente', 'historialClinico']);

        return view('psicologo.historial.create', compact('cita'));
    }

    public function store(HistorialClinicoRequest $request, Cita $cita): RedirectResponse
    {
        /** @var User $psicologo */
        $psicologo = $request->user();

        $this->citas->atenderSesion(
            $cita,
            $request->string('notas_sesion')->toString(),
            $request->input('avance'),
            $psicologo,
        );

        return redirect()->route('citas.show', $cita)
            ->with('status', 'Historial clinico registrado. Sesion marcada como atendida.');
    }
}
