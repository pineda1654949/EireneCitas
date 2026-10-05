<?php

namespace App\Http\Controllers;

use App\Enums\EstadoCita;
use App\Http\Requests\Citas\CancelarCitaRequest;
use App\Http\Requests\Citas\RegistrarCitaRequest;
use App\Http\Requests\Citas\ReprogramarCitaRequest;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Paciente;
use App\Models\Promocion;
use App\Models\User;
use App\Services\CitaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestion de citas: RF-01 (registro), RF-03 (reprogramar/cancelar) y
 * RF-04 (confirmacion). Los permisos los define CitaPolicy y las reglas
 * de negocio CitaService.
 */
class CitaController extends Controller
{
    public function __construct(private readonly CitaService $citas) {}

    public function index(Request $request): View
    {
        /** @var User $usuario */
        $usuario = $request->user();

        $filtros = $request->validate([
            'estado' => ['nullable', Rule::enum(EstadoCita::class)],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);

        $citas = Cita::with(['paciente', 'psicologo', 'especialidad'])
            ->visiblesPara($usuario)
            ->when($filtros['estado'] ?? null, fn ($q, $estado) => $q->where('estado', $estado))
            ->when($filtros['desde'] ?? null, fn ($q, $desde) => $q->whereDate('fecha', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn ($q, $hasta) => $q->whereDate('fecha', '<=', $hasta))
            ->when($filtros['buscar'] ?? null, fn ($q, $buscar) => $q->whereHas(
                'paciente',
                fn ($p) => $p->buscar($buscar),
            ))
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate(15)
            ->withQueryString();

        return view('citas.index', compact('citas', 'filtros'));
    }

    /**
     * RF-01 (paso 1): formulario de solicitud de cita.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Cita::class);

        /** @var User $usuario */
        $usuario = $request->user();

        return view('citas.create', [
            'especialidades' => Especialidad::orderBy('nombre')->get(),
            'promociones' => Promocion::activas()->orderBy('nombre')->get(),
            // El personal registra la cita a nombre de cualquier paciente.
            'pacientes' => $usuario->esPersonalAdministrativo()
                ? Paciente::orderBy('apellidos')->orderBy('nombres')->get()
                : null,
        ]);
    }

    public function store(RegistrarCitaRequest $request): RedirectResponse
    {
        /** @var User $usuario */
        $usuario = $request->user();

        $paciente = $usuario->esPersonalAdministrativo()
            ? Paciente::findOrFail($request->integer('paciente_id'))
            : $usuario->pacienteFicha;

        abort_if($paciente === null, 403);

        $cita = $this->citas->registrar($paciente, $request->validated(), $usuario);

        return redirect()->route('citas.show', $cita)
            ->with('status', 'Cita registrada correctamente. Queda pendiente de confirmacion de pago.');
    }

    public function show(Cita $cita): View
    {
        $this->authorize('view', $cita);

        $cita->load([
            'paciente', 'psicologo', 'especialidad', 'promocion', 'pagos',
            'reprogramaciones.usuario', 'historialClinico',
        ]);

        return view('citas.show', compact('cita'));
    }

    /**
     * RF-04: confirmacion (requiere pago validado).
     */
    public function confirmar(Cita $cita): RedirectResponse
    {
        $this->authorize('confirmar', $cita);

        $this->citas->confirmar($cita);

        return back()->with('status', 'Cita confirmada correctamente.');
    }

    /**
     * RF-03: reprogramacion (maximo configurable, 3 por defecto).
     */
    public function editarFecha(Cita $cita): View|RedirectResponse
    {
        $this->authorize('reprogramar', $cita);

        if (! $cita->puedeReprogramarse()) {
            return redirect()->route('citas.show', $cita)->withErrors([
                'fecha' => 'Esta cita ya no puede reprogramarse (limite alcanzado o cita cerrada).',
            ]);
        }

        $cita->load('psicologo');

        return view('citas.reprogramar', compact('cita'));
    }

    public function reprogramar(ReprogramarCitaRequest $request, Cita $cita): RedirectResponse
    {
        /** @var User $usuario */
        $usuario = $request->user();

        $this->citas->reprogramar(
            $cita,
            $request->string('fecha')->toString(),
            $request->string('hora')->toString(),
            $request->input('motivo'),
            $usuario,
        );

        return redirect()->route('citas.show', $cita)->with('status', 'Cita reprogramada correctamente.');
    }

    /**
     * RF-03: cancelacion.
     */
    public function confirmarCancelacion(Cita $cita): View|RedirectResponse
    {
        $this->authorize('cancelar', $cita);

        if (! $cita->puedeCancelarse()) {
            return redirect()->route('citas.show', $cita)->withErrors([
                'estado' => 'Esta cita ya fue cancelada o atendida; no puede cancelarse.',
            ]);
        }

        return view('citas.cancelar', compact('cita'));
    }

    public function cancelar(CancelarCitaRequest $request, Cita $cita): RedirectResponse
    {
        /** @var User $usuario */
        $usuario = $request->user();

        $this->citas->cancelar($cita, $request->input('motivo'), $usuario);

        return redirect()->route('citas.show', $cita)->with('status', 'Cita cancelada correctamente.');
    }
}
