<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoCita;
use App\Enums\EstadoPago;
use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * RF-08: reportes e indicadores de atencion.
 */
class ReporteController extends Controller
{
    public function index(Request $request): View
    {
        [$desde, $hasta] = $this->rango($request);

        $citasPorEstado = Cita::whereBetween('fecha', [$desde, $hasta])
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $citasPorPsicologo = Cita::whereBetween('fecha', [$desde, $hasta])
            ->join('users', 'users.id', '=', 'citas.psicologo_id')
            ->selectRaw('users.name, users.apellidos, count(*) as total')
            ->selectRaw('sum(case when citas.estado = ? then 1 else 0 end) as atendidas', [EstadoCita::Atendida->value])
            ->groupBy('users.id', 'users.name', 'users.apellidos')
            ->orderByDesc('total')
            ->get();

        $ingresosConfirmados = Pago::whereHas('cita', fn ($q) => $q->whereBetween('fecha', [$desde, $hasta]))
            ->where('estado', EstadoPago::Confirmado->value)
            ->sum('monto');

        $totalReprogramaciones = (int) Cita::whereBetween('fecha', [$desde, $hasta])->sum('numero_reprogramaciones');

        return view('admin.reportes.index', [
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
            'citasPorEstado' => $citasPorEstado,
            'totalCitas' => (int) $citasPorEstado->sum(),
            'citasPorPsicologo' => $citasPorPsicologo,
            'ingresosConfirmados' => $ingresosConfirmados,
            'totalReprogramaciones' => $totalReprogramaciones,
        ]);
    }

    /**
     * Exporta las citas del periodo a CSV (abre en Excel).
     */
    public function exportar(Request $request): StreamedResponse
    {
        [$desde, $hasta] = $this->rango($request);

        $nombre = "citas_{$desde->toDateString()}_{$hasta->toDateString()}.csv";

        return response()->streamDownload(function () use ($desde, $hasta) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF"); // BOM para que Excel reconozca UTF-8
            fputcsv($salida, ['ID', 'Fecha', 'Hora', 'Paciente', 'DNI', 'Psicologo', 'Especialidad', 'Estado', 'Reprogramaciones']);

            Cita::with(['paciente', 'psicologo', 'especialidad'])
                ->whereBetween('fecha', [$desde, $hasta])
                ->orderBy('fecha')->orderBy('hora')
                ->chunk(200, function ($citas) use ($salida) {
                    foreach ($citas as $cita) {
                        fputcsv($salida, [
                            $cita->id,
                            $cita->fecha->format('d/m/Y'),
                            $cita->hora_corta,
                            $cita->paciente->nombre_completo,
                            $cita->paciente->dni,
                            $cita->psicologo->nombre_completo,
                            $cita->especialidad?->nombre,
                            $cita->estado->etiqueta(),
                            $cita->numero_reprogramaciones,
                        ]);
                    }
                });

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function rango(Request $request): array
    {
        $datos = $request->validate([
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ]);

        return [
            Carbon::parse($datos['desde'] ?? now()->subDays(30)->toDateString())->startOfDay(),
            Carbon::parse($datos['hasta'] ?? now()->toDateString())->endOfDay(),
        ];
    }
}
