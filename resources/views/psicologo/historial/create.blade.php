@extends('layouts.panel')

@section('titulo', 'Registrar historial clinico')

@section('content')
    <div class="card border-0 shadow-sm" style="max-width: 650px;">
        <div class="card-body">
            <p class="text-muted">Paciente: <strong>{{ $cita->paciente->nombre_completo }}</strong> &middot; Sesion: {{ $cita->fecha->format('d/m/Y') }}</p>
            <form method="POST" action="{{ route('psicologo.historial.store', $cita) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Avance / estado del paciente</label>
                    <input type="text" name="avance" class="form-control" placeholder="Ej. Inicial, En progreso, Alta">
                </div>
                <div class="mb-3">
                    <label class="form-label">Notas de la sesion</label>
                    <textarea name="notas_sesion" class="form-control" rows="6" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Guardar y marcar como atendida</button>
                <a href="{{ route('citas.show', $cita) }}" class="btn btn-link">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
