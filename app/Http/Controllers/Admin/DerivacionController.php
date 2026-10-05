<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoDerivacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DerivarPacienteRequest;
use App\Models\Derivacion;
use App\Models\Paciente;
use App\Models\User;
use App\Services\DerivacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * RF-05: la administracion deriva pacientes y sigue sus respuestas.
 */
class DerivacionController extends Controller
{
    public function __construct(private readonly DerivacionService $derivaciones) {}

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'estado' => ['nullable', Rule::enum(EstadoDerivacion::class)],
        ]);

        $derivaciones = Derivacion::with(['paciente', 'psicologo', 'especialidad', 'derivadoPor'])
            ->when($filtros['estado'] ?? null, fn ($q, $estado) => $q->where('estado', $estado))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.derivaciones.index', compact('derivaciones', 'filtros'));
    }

    public function store(DerivarPacienteRequest $request, Paciente $paciente): RedirectResponse
    {
        /** @var User $usuario */
        $usuario = $request->user();

        $this->derivaciones->derivar(
            $paciente,
            $request->integer('psicologo_id'),
            $request->filled('especialidad_id') ? $request->integer('especialidad_id') : null,
            $request->input('observaciones'),
            $usuario,
        );

        return redirect()->route('admin.pacientes.edit', $paciente)
            ->with('status', 'Paciente derivado. El psicólogo recibirá un aviso para aceptar o rechazar.');
    }
}
