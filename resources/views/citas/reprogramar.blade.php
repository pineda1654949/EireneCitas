@extends('layouts.panel')

@section('titulo', 'Reprogramar cita')

@section('content')
    <div class="card border-0 shadow-sm" style="max-width: 500px;">
        <div class="card-body">
            <p class="text-muted">Reprogramaciones usadas: <strong>{{ $cita->numero_reprogramaciones }} / {{ \App\Models\Cita::MAX_REPROGRAMACIONES }}</strong></p>
            <p class="text-muted small mb-3">Psicologo: {{ $cita->psicologo->name }} {{ $cita->psicologo->apellidos }}</p>
            <form method="POST" action="{{ route('citas.reprogramar', $cita) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nueva fecha</label>
                    <input type="date" id="fecha" name="fecha" class="form-control" min="{{ now()->toDateString() }}" value="{{ old('fecha') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nueva hora disponible</label>
                    <select id="hora" name="hora" class="form-select" required>
                        <option value="">Elige una fecha primero...</option>
                    </select>
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

@push('scripts')
<script>
const fechaInput = document.getElementById('fecha');
const horaSelect = document.getElementById('hora');
const apiUrl = @json(url('/api'));
const horaAnterior = @json(old('hora'));

// RF-02: solo se ofrecen las horas libres del mismo psicologo en la nueva fecha.
async function cargarHoras() {
    if (!fechaInput.value) {
        return;
    }
    horaSelect.innerHTML = '<option value="">Cargando horas disponibles...</option>';

    const params = new URLSearchParams({
        psicologo_id: @json($cita->psicologo_id),
        fecha: fechaInput.value,
        cita_id: @json($cita->id),
    });
    const res = await fetch(`${apiUrl}/horas-disponibles?${params.toString()}`, {
        headers: { 'Accept': 'application/json' }
    });
    const horas = await res.json();

    if (horas.length === 0) {
        horaSelect.innerHTML = '<option value="">No hay horas disponibles ese dia</option>';
        return;
    }

    horaSelect.innerHTML = '<option value="">Selecciona una hora...</option>';
    horas.forEach(h => {
        const opt = document.createElement('option');
        opt.value = h;
        opt.textContent = h;
        opt.selected = h === horaAnterior;
        horaSelect.appendChild(opt);
    });
}

fechaInput.addEventListener('change', cargarHoras);
cargarHoras();
</script>
@endpush
