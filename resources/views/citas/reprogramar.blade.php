@extends('layouts.panel')

@section('titulo', 'Reprogramar cita')

@section('content')
    <div class="card border-0 shadow-sm" style="max-width: 500px;">
        <div class="card-body">
            <p class="text-muted">Reprogramaciones usadas: <strong>{{ $cita->numero_reprogramaciones }} / {{ \App\Models\Cita::MAX_REPROGRAMACIONES }}</strong></p>
            <form method="POST" action="{{ route('citas.reprogramar', $cita) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nueva fecha</label>
                    <input type="date" name="fecha" class="form-control" min="{{ now()->toDateString() }}" value="{{ old('fecha') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nueva hora</label>
                    <input type="time" name="hora" class="form-control" value="{{ old('hora') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Motivo (opcional)</label>
                    <textarea name="motivo" class="form-control" rows="2">{{ old('motivo') }}</textarea>
                </div>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-arrow-repeat me-1"></i> Confirmar reprogramacion
                </button>
                <a href="{{ route('citas.show', $cita) }}" class="btn btn-link">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
