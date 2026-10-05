<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Promocion;
use App\Models\Reprogramacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CitaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * RF-01 (paso 1): formulario de solicitud de cita.
     * Disponible para paciente, recepcionista y administrador.
     */
    public function create()
    {
        $especialidades = Especialidad::orderBy('nombre')->get();
        $promociones = Promocion::where('activa', true)->orderBy('nombre')->get();

        // La recepcionista y el administrador pueden crear la cita a nombre de cualquier paciente
        $pacientes = in_array(Auth::user()->role, ['recepcionista', 'administrador'])
            ? Paciente::orderBy('nombres')->get()
            : null;

        return view('citas.create', compact('especialidades', 'promociones', 'pacientes'));
    }

    /**
     * RF-01: Registro de citas.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $reglas = [
            'especialidad_id' => 'required|exists:especialidades,id',
            'psicologo_id' => 'required|exists:users,id',
            'promocion_id' => 'nullable|exists:promociones,id',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required',
            'motivo_consulta' => 'nullable|string',
        ];

        if (in_array($user->role, ['recepcionista', 'administrador'])) {
            $reglas['paciente_id'] = 'required|exists:pacientes,id';
        }

        $datos = $request->validate($reglas);

        $paciente = in_array($user->role, ['recepcionista', 'administrador'])
            ? Paciente::findOrFail($datos['paciente_id'])
            : Paciente::where('user_id', $user->id)->firstOrFail();

        // RF-02: verificar disponibilidad antes de confirmar el registro.
        if (!$this->horaDisponible($datos['psicologo_id'], $datos['fecha'], $datos['hora'])) {
            return back()->withInput()->withErrors([
                'hora' => 'Ese horario ya no esta disponible para el psicologo. Elige otro horario disponible.',
            ]);
        }

        $cita = Cita::create([
            'paciente_id' => $paciente->id,
            'psicologo_id' => $datos['psicologo_id'],
            'especialidad_id' => $datos['especialidad_id'],
            'promocion_id' => $datos['promocion_id'] ?? null,
            'fecha' => $datos['fecha'],
            'hora' => $datos['hora'],
            'motivo_consulta' => $datos['motivo_consulta'] ?? null,
            'estado' => 'pendiente',
            'creado_por' => $user->id,
        ]);

        return redirect()->route('citas.show', $cita)
            ->with('status', 'Cita registrada correctamente. Queda pendiente de confirmacion de pago.');
    }

    /**
     * Listado de "Mis citas" (paciente) o todas las citas (recepcionista/admin).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Cita::with(['paciente', 'psicologo', 'especialidad']);

        if ($user->role === 'paciente') {
            $paciente = Paciente::where('user_id', $user->id)->first();
            $query->where('paciente_id', optional($paciente)->id ?? 0);
        } elseif ($user->role === 'psicologo') {
            $query->where('psicologo_id', $user->id);
        } elseif ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $citas = $query->orderBy('fecha', 'desc')->orderBy('hora', 'desc')->paginate(15);

        return view('citas.index', compact('citas'));
    }

    public function show(Cita $cita)
    {
        $this->autorizarAccesoCita($cita);
        $cita->load(['paciente', 'psicologo', 'especialidad', 'promocion', 'pagos', 'reprogramaciones', 'historialClinico']);
        return view('citas.show', compact('cita'));
    }

    /**
     * RF-04: Confirmacion de citas (recepcionista valida el pago y confirma).
     */
    public function confirm(Cita $cita)
    {
        if (!in_array(Auth::user()->role, ['recepcionista', 'administrador'])) {
            abort(403);
        }

        if (!in_array($cita->estado, ['pendiente', 'reprogramada'])) {
            return back()->withErrors(['estado' => 'Solo se pueden confirmar citas pendientes o reprogramadas.']);
        }

        if (!$cita->pagoConfirmado()) {
            return back()->withErrors(['estado' => 'No se puede confirmar la cita: primero registra y valida su pago.']);
        }

        $cita->update(['estado' => 'confirmada']);

        return back()->with('status', 'Cita confirmada correctamente.');
    }

    /**
     * RF-03: Reprogramacion de citas, con limite maximo de 3 veces.
     */
    public function formReprogramar(Cita $cita)
    {
        $this->autorizarAccesoCita($cita);

        if (!$cita->puedeReprogramarse()) {
            return back()->withErrors(['fecha' => 'Esta cita ya no puede reprogramarse (limite de 3 veces alcanzado o cita finalizada/cancelada).']);
        }

        return view('citas.reprogramar', compact('cita'));
    }

    public function reprogramar(Request $request, Cita $cita)
    {
        $this->autorizarAccesoCita($cita);

        if (!$cita->puedeReprogramarse()) {
            return back()->withErrors(['fecha' => 'Se alcanzo el limite maximo de 3 reprogramaciones para esta cita.']);
        }

        $datos = $request->validate([
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required',
            'motivo' => 'nullable|string',
        ]);

        // RF-02: la nueva fecha/hora debe estar libre en el horario del psicologo.
        if (!$this->horaDisponible($cita->psicologo_id, $datos['fecha'], $datos['hora'], $cita->id)) {
            return back()->withInput()->withErrors([
                'hora' => 'Ese horario no esta disponible para el psicologo. Elige otro horario disponible.',
            ]);
        }

        DB::transaction(function () use ($cita, $datos) {
            Reprogramacion::create([
                'cita_id' => $cita->id,
                'realizado_por' => Auth::id(),
                'tipo' => 'reprogramacion',
                'fecha_anterior' => $cita->fecha,
                'hora_anterior' => $cita->hora,
                'fecha_nueva' => $datos['fecha'],
                'hora_nueva' => $datos['hora'],
                'motivo' => $datos['motivo'] ?? null,
            ]);

            $cita->update([
                'fecha' => $datos['fecha'],
                'hora' => $datos['hora'],
                'estado' => 'reprogramada',
                'numero_reprogramaciones' => $cita->numero_reprogramaciones + 1,
            ]);
        });

        return redirect()->route('citas.show', $cita)->with('status', 'Cita reprogramada correctamente.');
    }

    /**
     * RF-03: Cancelacion de citas.
     */
    public function formCancelar(Cita $cita)
    {
        $this->autorizarAccesoCita($cita);

        if (!$cita->puedeCancelarse()) {
            return back()->withErrors(['estado' => 'Esta cita ya fue cancelada o atendida; no puede cancelarse.']);
        }

        return view('citas.cancelar', compact('cita'));
    }

    public function cancelar(Request $request, Cita $cita)
    {
        $this->autorizarAccesoCita($cita);

        if (!$cita->puedeCancelarse()) {
            return redirect()->route('citas.show', $cita)
                ->withErrors(['estado' => 'Esta cita ya fue cancelada o atendida; no puede cancelarse.']);
        }

        $datos = $request->validate(['motivo' => 'nullable|string']);

        DB::transaction(function () use ($cita, $datos) {
            Reprogramacion::create([
                'cita_id' => $cita->id,
                'realizado_por' => Auth::id(),
                'tipo' => 'cancelacion',
                'fecha_anterior' => $cita->fecha,
                'hora_anterior' => $cita->hora,
                'motivo' => $datos['motivo'] ?? null,
            ]);

            $cita->update(['estado' => 'cancelada']);
        });

        return redirect()->route('citas.index')->with('status', 'Cita cancelada correctamente.');
    }

    /**
     * La hora debe ser uno de los bloques libres del horario del psicologo.
     */
    private function horaDisponible($psicologoId, $fecha, $hora, $ignorarCitaId = null): bool
    {
        return in_array(
            substr($hora, 0, 5),
            Horario::horasDisponibles($psicologoId, $fecha, $ignorarCitaId)
        );
    }

    private function autorizarAccesoCita(Cita $cita)
    {
        $user = Auth::user();

        if (in_array($user->role, ['administrador', 'recepcionista'])) {
            return;
        }

        if ($user->role === 'psicologo' && $cita->psicologo_id === $user->id) {
            return;
        }

        if ($user->role === 'paciente') {
            $paciente = Paciente::where('user_id', $user->id)->first();
            if ($paciente && $cita->paciente_id === $paciente->id) {
                return;
            }
        }

        abort(403, 'No tienes acceso a esta cita.');
    }
}