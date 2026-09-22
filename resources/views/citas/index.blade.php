@extends('layouts.panel')

@section('titulo', 'Citas')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th><th>Hora</th><th>Paciente</th><th>Psicologo</th>
                        <th>Especialidad</th><th>Estado</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($citas as $cita)
                        <tr>
                            <td>{{ $cita->fecha->format('d/m/Y') }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($cita->hora)->format('H:i') }}</td>
                            <td>{{ $cita->paciente->nombre_completo }}</td>
                            <td>{{ $cita->psicologo->name }} {{ $cita->psicologo->apellidos }}</td>
                            <td>{{ optional($cita->especialidad)->nombre }}</td>
                            <td><span class="badge bg-{{ $cita->estado_badge }}">{{ ucfirst($cita->estado) }}</span></td>
                            <td class="text-end"><a href="{{ route('citas.show', $cita) }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No hay citas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">
            {{ $citas->links() }}
        </div>
    </div>
@endsection
