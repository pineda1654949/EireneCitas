@extends('layouts.panel')

@section('titulo', 'Mi panel')

@section('content')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-1">Hola, {{ auth()->user()->name }} 👋</h5>
                <p class="text-muted mb-0">¿Necesitas una sesion? Solicita tu cita en pocos pasos.</p>
            </div>
            <a href="{{ route('citas.create') }}" class="btn btn-primary">
                <i class="bi bi-calendar-plus me-1"></i> Solicitar cita
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Mis ultimas citas</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Hora</th><th>Psicologo</th><th>Especialidad</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($misCitas as $cita)
                        <tr>
                            <td>{{ $cita->fecha->format('d/m/Y') }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($cita->hora)->format('H:i') }}</td>
                            <td>{{ $cita->psicologo->name }} {{ $cita->psicologo->apellidos }}</td>
                            <td>{{ optional($cita->especialidad)->nombre }}</td>
                            <td><span class="badge bg-{{ $cita->estado_badge }}">{{ ucfirst($cita->estado) }}</span></td>
                            <td class="text-end"><a href="{{ route('citas.show', $cita) }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aun no tienes citas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
