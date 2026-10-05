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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Control de pagos (RF-04): se registra el metodo, la cuota y el voucher, y
 * el personal lo valida antes de confirmar la cita (reemplaza la verificacion
 * manual por WhatsApp del AS-IS).
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
        $this->authorize('reportarPago', $cita);

        $cita->load(['paciente', 'psicologo', 'promocion', 'pagos']);

        $vigentes = $cita->pagos->reject(fn (Pago $pago) => $pago->estado === EstadoPago::Rechazado);

        return view('admin.pagos.create', [
            'cita' => $cita,
            'metodos' => MetodoPago::cases(),
            'maxCuotas' => $cita->promocion?->cuotasPermitidas() ?? 1,
            // Si ya hay cuotas registradas, el plan de pago queda fijado.
            'planVigente' => $vigentes->first()?->total_cuotas,
            'cuotasRegistradas' => $vigentes->count(),
        ]);
    }

    public function store(PagoRequest $request, Cita $cita): RedirectResponse
    {
        /** @var User $usuario */
        $usuario = $request->user();

        $this->citas->registrarPago($cita, $request->validated(), $usuario, $request->file('comprobante'));

        return redirect()->route('citas.show', $cita)->with('status', $usuario->esPersonalAdministrativo()
            ? 'Pago registrado, pendiente de validación.'
            : 'Recibimos tu voucher. La clínica lo validará y te avisará por correo.');
    }

    /**
     * Muestra el voucher solo a quien puede ver la cita. Los archivos viven en
     * el disco privado y nunca se sirven desde /public (CP-SYS-34).
     */
    public function comprobante(Pago $pago): StreamedResponse
    {
        $this->authorize('view', $pago->cita);

        abort_unless($pago->comprobante_path && Storage::disk('local')->exists($pago->comprobante_path), 404);

        return Storage::disk('local')->response($pago->comprobante_path, $pago->comprobante_nombre, [
            'X-Content-Type-Options' => 'nosniff',
            // El archivo se muestra aislado: no puede ejecutar scripts ni cargar recursos.
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox",
        ]);
    }

    public function validar(Request $request, Pago $pago): RedirectResponse
    {
        /** @var User $usuario */
        $usuario = $request->user();

        $confirmada = $this->citas->validarPago($pago, $usuario);

        return back()->with('status', $confirmada
            ? 'Pago validado y cita confirmada.'
            : 'Pago validado. La cita no cambió de estado porque ya está '.$pago->cita->estado->etiqueta().'.');
    }

    public function rechazar(Request $request, Pago $pago): RedirectResponse
    {
        /** @var User $usuario */
        $usuario = $request->user();

        $this->citas->rechazarPago($pago, $usuario);

        return back()->with('status', 'Pago marcado como rechazado.');
    }
}
