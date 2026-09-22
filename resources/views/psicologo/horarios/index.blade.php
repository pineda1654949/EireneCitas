@extends('layouts.panel')

@section('titulo', 'Mi disponibilidad')

@section('content')
    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Agregar bloque de horario</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('psicologo.horarios.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Dia de la semana</label>
                            <select name="dia_semana" class="form-select" required>
                                @foreach(\App\Models\Horario::DIAS as $num => $nombre)
                                    <option value="{{ $num }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hora de inicio</label>
                            <input type="time" name="hora_inicio" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hora de fin</label>
                            <input type="time" name="hora_fin" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100">Agregar horario</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Mis horarios configurados</div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light"><tr><th>Dia</th><th>Inicio</th><th>Fin</th><th></th></tr></thead>
                        <tbody>
                            @forelse($horarios as $h)
                                <tr>
                                    <td>{{ \App\Models\Horario::DIAS[$h->dia_semana] }}</td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($h->hora_inicio)->format('H:i') }}</td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($h->hora_fin)->format('H:i') }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('psicologo.horarios.destroy', $h) }}" method="POST" onsubmit="return confirm('¿Eliminar este horario?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Aun no has configurado horarios.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
