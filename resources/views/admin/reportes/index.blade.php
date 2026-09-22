@extends('layouts.panel')

@section('titulo', 'Reportes e indicadores')

@section('content')
    <form method="GET" class="row g-2 mb-4">
        <div class="col-auto">
            <label class="form-label small">Desde</label>
            <input type="date" name="desde" class="form-control" value="{{ $desde }}">
        </div>
        <div class="col-auto">
            <label class="form-label small">Hasta</label>
            <input type="date" name="hasta" class="form-control" value="{{ $hasta }}">
        </div>
        <div class="col-auto d-flex align-items-end">
            <button class="btn btn-primary">Filtrar</button>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Ingresos confirmados</div>
                <div class="fs-4 fw-bold text-success">S/ {{ number_format($ingresosConfirmados, 2) }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Total reprogramaciones</div>
                <div class="fs-4 fw-bold text-warning">{{ $totalReprogramaciones }}</div>
            </div></div>
        </div>
        @foreach(['pendiente' => 'warning', 'confirmada' => 'success', 'atendida' => 'primary', 'cancelada' => 'danger'] as $estado => $color)
            <div class="col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted small">Citas {{ $estado }}s</div>
                    <div class="fs-4 fw-bold text-{{ $color }}">{{ $citasPorEstado[$estado] ?? 0 }}</div>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Citas atendidas por psicologo</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Psicologo</th><th>Total de citas</th></tr></thead>
                <tbody>
                    @forelse($citasPorPsicologo as $fila)
                        <tr><td>{{ $fila->name }} {{ $fila->apellidos }}</td><td>{{ $fila->total }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted py-4">Sin datos en el rango seleccionado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
