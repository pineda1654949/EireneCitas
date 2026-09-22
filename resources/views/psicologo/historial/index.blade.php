@extends('layouts.panel')

@section('titulo', 'Historial clinico del paciente')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Fecha</th><th>Psicologo</th><th>Avance</th><th>Notas</th></tr></thead>
                <tbody>
                    @forelse($historiales as $h)
                        <tr>
                            <td>{{ $h->created_at->format('d/m/Y') }}</td>
                            <td>{{ $h->psicologo->name }} {{ $h->psicologo->apellidos }}</td>
                            <td>{{ $h->avance ?: '-' }}</td>
                            <td>{{ $h->notas_sesion }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Este paciente aun no tiene historial clinico registrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
