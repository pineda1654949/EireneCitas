@extends('layouts.panel')

@section('titulo', 'Panel del administrador')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Pacientes registrados</div>
                <div class="fs-3 fw-bold">{{ $totalPacientes }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Psicologos activos</div>
                <div class="fs-3 fw-bold">{{ $totalPsicologos }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Citas pendientes</div>
                <div class="fs-3 fw-bold text-warning">{{ $citasPendientes }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Citas confirmadas</div>
                <div class="fs-3 fw-bold text-success">{{ $citasConfirmadas }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Sesiones atendidas</div>
                <div class="fs-4 fw-bold text-primary">{{ $citasAtendidas }}</div>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Citas canceladas</div>
                <div class="fs-4 fw-bold text-danger">{{ $citasCanceladas }}</div>
            </div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Ultimas citas registradas</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Paciente</th><th>Psicologo</th><th>Fecha</th><th>Hora</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    @forelse($ultimasCitas as $cita)
                        <tr>
                            <td><a href="{{ route('citas.show', $cita) }}">{{ $cita->paciente->nombre_completo }}</a></td>
                            <td>{{ $cita->psicologo->name }} {{ $cita->psicologo->apellidos }}</td>
                            <td>{{ $cita->fecha->format('d/m/Y') }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($cita->hora)->format('H:i') }}</td>
                            <td><span class="badge bg-{{ $cita->estado_badge }}">{{ ucfirst($cita->estado) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Aun no hay citas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
