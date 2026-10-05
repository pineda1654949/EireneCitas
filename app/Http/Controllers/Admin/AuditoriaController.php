<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Consulta de la bitacora de auditoria (solo administrador).
 */
class AuditoriaController extends Controller
{
    /** Modelos auditados que se pueden filtrar. */
    private const TIPOS = ['Cita', 'Pago', 'Paciente', 'User', 'Promocion', 'Horario', 'HistorialClinico'];

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'evento' => ['nullable', 'string', 'max:50'],
            'tipo' => ['nullable', 'in:'.implode(',', self::TIPOS)],
            'usuario' => ['nullable', 'integer'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $registros = Auditoria::with('usuario')
            ->when($filtros['evento'] ?? null, fn ($q, $evento) => $q->where('evento', $evento))
            ->when($filtros['tipo'] ?? null, fn ($q, $tipo) => $q->where('auditable_type', 'App\\Models\\'.$tipo))
            ->when($filtros['usuario'] ?? null, fn ($q, $usuario) => $q->where('user_id', $usuario))
            ->when($filtros['desde'] ?? null, fn ($q, $desde) => $q->whereDate('created_at', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn ($q, $hasta) => $q->whereDate('created_at', '<=', $hasta))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.auditoria.index', [
            'registros' => $registros,
            'filtros' => $filtros,
            'eventos' => Auditoria::query()->distinct()->orderBy('evento')->pluck('evento'),
            'tipos' => self::TIPOS,
        ]);
    }
}
