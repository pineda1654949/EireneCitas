@extends('layouts.panel')

@section('titulo', 'Panel de recepcion')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Citas de hoy</div>
                <div class="fs-3 fw-bold">{{ $citasHoy->count() }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Citas pendientes de confirmar</div>
                <div class="fs-3 fw-bold text-warning">{{ $citasPendientes }}</div>
            </div></div>
        </div>
        <div class="col-md-4 d-flex align-items-stretch">
            <a href="{{ route('citas.create') }}" class="card border-0 shadow-sm text-decoration-none w-100">
                <div class="card-body d-flex align-items-center justify-content-center text-primary fw-semibold">
                    <i class="bi bi-calendar-plus me-2 fs-4"></i> Registrar nueva cita
                </div>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Agenda de hoy ({{ now()->translatedFormat('d/m/Y') }})</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Hora</th><th>Paciente</th><th>Psicologo</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($citasHoy as $cita)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($cita->hora)->format('H:i') }}</td>
                            <td>{{ $cita->paciente->nombre_completo }}</td>
                            <td>{{ $cita->psicologo->name }} {{ $cita->psicologo->apellidos }}</td>
                            <td><span class="badge bg-{{ $cita->estado_badge }}">{{ ucfirst($cita->estado) }}</span></td>
                            <td class="text-end"><a href="{{ route('citas.show', $cita) }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay citas programadas para hoy.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
