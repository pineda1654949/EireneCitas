<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Control de pagos (Tabla 1: "Se registra el metodo de pago elegido y el
 * estado de cada cuota"). Antes de confirmar una cita se debe validar el pago.
 */
class PagoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador,recepcionista']);
    }

    public function index(Request $request)
    {
        $query = Pago::with(['cita.paciente', 'cita.psicologo']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $pagos = $query->latest()->paginate(15);

        return view('admin.pagos.index', compact('pagos'));
    }

    public function create(Cita $cita)
    {
        return view('admin.pagos.create', compact('cita'));
    }

    public function store(Request $request, Cita $cita)
    {
        $datos = $request->validate([
            'monto' => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:tarjeta,yape_plin,transferencia,efectivo',
            'numero_comprobante' => 'nullable|string|max:255',
        ]);

        Pago::create([
            'cita_id' => $cita->id,
            'monto' => $datos['monto'],
            'metodo_pago' => $datos['metodo_pago'],
            'numero_comprobante' => $datos['numero_comprobante'] ?? null,
            'estado' => 'pendiente',
        ]);

        return redirect()->route('citas.show', $cita)->with('status', 'Pago registrado, pendiente de validacion.');
    }

    /**
     * Valida el pago (reemplaza la verificacion visual manual del AS-IS)
     * y automaticamente puede confirmar la cita asociada.
     */
    public function validar(Pago $pago)
    {
        $pago->update([
            'estado' => 'confirmado',
            'fecha_pago' => now(),
            'validado_por' => Auth::id(),
        ]);

        $pago->cita->update(['estado' => 'confirmada']);

        return back()->with('status', 'Pago validado y cita confirmada.');
    }

    public function rechazar(Pago $pago)
    {
        $pago->update(['estado' => 'rechazado', 'validado_por' => Auth::id()]);
        return back()->with('status', 'Pago marcado como rechazado.');
    }
}
