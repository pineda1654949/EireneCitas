@extends('layouts.panel')

@section('titulo', 'Cancelar cita')

@section('content')
    <div class="card border-0 shadow-sm" style="max-width: 500px;">
        <div class="card-body">
            <p>¿Estas seguro de cancelar la cita del <strong>{{ $cita->fecha->format('d/m/Y') }}</strong> a las <strong>{{ \Illuminate\Support\Carbon::parse($cita->hora)->format('H:i') }}</strong>?</p>
            <form method="POST" action="{{ route('citas.cancelar', $cita) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Motivo de cancelacion (opcional)</label>
                    <textarea name="motivo" class="form-control" rows="3">{{ old('motivo') }}</textarea>
                </div>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-x-circle me-1"></i> Confirmar cancelacion
                </button>
                <a href="{{ route('citas.show', $cita) }}" class="btn btn-link">Volver</a>
            </form>
        </div>
    </div>
@endsection
