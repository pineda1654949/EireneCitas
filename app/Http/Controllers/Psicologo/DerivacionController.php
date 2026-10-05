<?php

namespace App\Http\Controllers\Psicologo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Psicologo\RechazarDerivacionRequest;
use App\Models\Derivacion;
use App\Models\User;
use App\Services\DerivacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * RF-05: el psicologo acepta o rechaza las derivaciones recibidas.
 */
class DerivacionController extends Controller
{
    public function __construct(private readonly DerivacionService $derivaciones) {}

    public function index(Request $request): View
    {
        /** @var User $psicologo */
        $psicologo = $request->user();

        $derivaciones = $psicologo->derivacionesRecibidas()
            ->with(['paciente', 'especialidad', 'derivadoPor'])
            ->orderByRaw("case when estado = 'pendiente' then 0 else 1 end")
            ->latest('id')
            ->paginate(20);

        return view('psicologo.derivaciones.index', compact('derivaciones'));
    }

    public function aceptar(Derivacion $derivacion): RedirectResponse
    {
        $this->authorize('responder', $derivacion);

        $this->derivaciones->aceptar($derivacion);

        return back()->with('status', 'Derivación aceptada. El paciente quedó asignado a tu cargo.');
    }

    public function rechazar(RechazarDerivacionRequest $request, Derivacion $derivacion): RedirectResponse
    {
        $this->derivaciones->rechazar($derivacion, $request->string('motivo_rechazo')->toString());

        return back()->with('status', 'Derivación rechazada. La clínica recibirá tu motivo.');
    }
}
