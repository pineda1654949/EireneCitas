<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * RF-08: Generacion de reportes de atencion.
 * Actividad TO-BE 16: "Actualizar dashboard de indicadores" (Sistema).
 */
class ReporteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador,recepcionista']);
    }

    public function index(Request $request)
    {
        $desde = $request->input('desde', now()->subDays(30)->toDateString());
        $hasta = $request->input('hasta', now()->toDateString());

        $citasPorEstado = Cita::whereBetween('fecha', [$desde, $hasta])
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $citasPorPsicologo = Cita::whereBetween('fecha', [$desde, $hasta])
            ->join('users', 'users.id', '=', 'citas.psicologo_id')
            ->select('users.name', 'users.apellidos', DB::raw('count(*) as total'))
            ->groupBy('users.id', 'users.name', 'users.apellidos')
            ->orderByDesc('total')
            ->get();

        $ingresosConfirmados = Pago::whereHas('cita', function ($q) use ($desde, $hasta) {
            $q->whereBetween('fecha', [$desde, $hasta]);
        })->where('estado', 'confirmado')->sum('monto');

        $totalReprogramaciones = Cita::whereBetween('fecha', [$desde, $hasta])->sum('numero_reprogramaciones');

        return view('admin.reportes.index', compact(
            'desde', 'hasta', 'citasPorEstado', 'citasPorPsicologo', 'ingresosConfirmados', 'totalReprogramaciones'
        ));
    }
}
