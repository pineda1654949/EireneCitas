@extends('layouts.panel')

@section('titulo', 'Registrar pago')

@section('content')
    <div class="card border-0 shadow-sm" style="max-width: 600px;">
        <div class="card-body">
            <p class="text-muted">Cita #{{ $cita->id }} - {{ $cita->paciente->nombre_completo }} - {{ $cita->fecha->format('d/m/Y') }}</p>
            <form method="POST" action="{{ route('pagos.store', $cita) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Monto (S/)</label>
                    <input type="number" step="0.01" min="0" name="monto" class="form-control"
                           value="{{ old('monto', optional($cita->promocion)->precio ?? 80) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Metodo de pago</label>
                    <select name="metodo_pago" class="form-select" required>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="yape_plin">Yape / Plin</option>
                        <option value="transferencia">Transferencia bancaria</option>
                        <option value="efectivo">Efectivo</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Numero de comprobante / operacion (opcional)</label>
                    <input type="text" name="numero_comprobante" class="form-control" value="{{ old('numero_comprobante') }}">
                </div>
                <button type="submit" class="btn btn-primary">Registrar pago</button>
                <a href="{{ route('citas.show', $cita) }}" class="btn btn-link">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
