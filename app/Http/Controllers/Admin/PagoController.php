<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoPago;
use App\Enums\MetodoPago;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PagoRequest;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\User;
use App\Services\CitaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Control de pagos: se registra el metodo elegido y se valida antes de
 * confirmar la cita (reemplaza la verificacion manual del AS-IS).
 */
class PagoController extends Controller
{
    public function __construct(private readonly CitaService $citas) {}

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'estado' => ['nullable', Rule::enum(EstadoPago::class)],
        ]);

        $pagos = Pago::with(['cita.paciente', 'cita.psicologo', 'validadoPor'])
            ->when($filtros['estado'] ?? null, fn ($q, $estado) => $q->where('estado', $estado))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.pagos.index', compact('pagos', 'filtros'));
    }

    public function create(Cita $cita): View
    {
        $this->authorize('gestionarPagos', $cita);

        $cita->load(['paciente', 'psicologo', 'promocion']);

        return view('admin.pagos.create', [
            'cita' => $cita,
            'metodos' => MetodoPago::cases(),
        ]);
    }

    public function store(PagoRequest $request, Cita $cita): RedirectResponse
    {
        $this->citas->registrarPago($cita, $request->validated());

        return redirect()->route('citas.show', $cita)->with('status', 'Pago registrado, pendiente de validacion.');
    }

    public function validar(Request $request, Pago $pago): RedirectResponse
    {
        /** @var User $usuario */
        $usuario = $request->user();

        $confirmada = $this->citas->validarPago($pago, $usuario);

        return back()->with('status', $confirmada
            ? 'Pago validado y cita confirmada.'
            : 'Pago validado. La cita no cambio de estado porque ya esta '.$pago->cita->estado->etiqueta().'.');
    }

    public function rechazar(Request $request, Pago $pago): RedirectResponse
    {
        /** @var User $usuario */
        $usuario = $request->user();

        $this->citas->rechazarPago($pago, $usuario);

        return back()->with('status', 'Pago marcado como rechazado.');
    }
}
