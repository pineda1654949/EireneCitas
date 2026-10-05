@extends('layouts.panel')

@section('titulo', 'Detalle de la cita')

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Cita #{{ $cita->id }}</span>
                    <span class="badge bg-{{ $cita->estado_badge }} fs-6">{{ ucfirst($cita->estado) }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-2"><div class="col-4 text-muted">Paciente</div><div class="col-8">{{ $cita->paciente->nombre_completo }}</div></div>
                    <div class="row mb-2"><div class="col-4 text-muted">Psicologo</div><div class="col-8">{{ $cita->psicologo->name }} {{ $cita->psicologo->apellidos }}</div></div>
                    <div class="row mb-2"><div class="col-4 text-muted">Especialidad</div><div class="col-8">{{ optional($cita->especialidad)->nombre ?? '-' }}</div></div>
                    <div class="row mb-2"><div class="col-4 text-muted">Promocion</div><div class="col-8">{{ optional($cita->promocion)->nombre ?? 'Sesion individual' }}</div></div>
                    <div class="row mb-2"><div class="col-4 text-muted">Fecha y hora</div><div class="col-8">{{ $cita->fecha->format('d/m/Y') }} - {{ \Illuminate\Support\Carbon::parse($cita->hora)->format('H:i') }}</div></div>
                    <div class="row mb-2"><div class="col-4 text-muted">Motivo de consulta</div><div class="col-8">{{ $cita->motivo_consulta ?: '-' }}</div></div>
                    <div class="row mb-2"><div class="col-4 text-muted">Reprogramaciones</div><div class="col-8">{{ $cita->numero_reprogramaciones }} / {{ \App\Models\Cita::MAX_REPROGRAMACIONES }}</div></div>
                    @if($cita->enlace_meet)
                        <div class="row mb-2"><div class="col-4 text-muted">Enlace de sesion</div><div class="col-8"><a href="{{ $cita->enlace_meet }}" target="_blank">{{ $cita->enlace_meet }}</a></div></div>
                    @endif
                </div>
            </div>

            @if($cita->historialClinico)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold">Historial clinico de la sesion</div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Avance:</strong> {{ $cita->historialClinico->avance ?: '-' }}</p>
                        <p class="mb-0">{{ $cita->historialClinico->notas_sesion }}</p>
                    </div>
                </div>
            @endif

            @if($cita->reprogramaciones->isNotEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold">Historial de cambios</div>
                    <div class="table-responsive">
                        <table class="table mb-0 small align-middle">
                            <thead class="table-light"><tr><th>Tipo</th><th>Fecha anterior</th><th>Fecha nueva</th><th>Motivo</th><th>Fecha registro</th></tr></thead>
                            <tbody>
                                @foreach($cita->reprogramaciones as $r)
                                    <tr>
                                        <td>{{ ucfirst($r->tipo) }}</td>
                                        <td>{{ $r->fecha_anterior }} {{ $r->hora_anterior }}</td>
                                        <td>{{ $r->fecha_nueva ?? '-' }} {{ $r->hora_nueva }}</td>
                                        <td>{{ $r->motivo ?: '-' }}</td>
                                        <td>{{ $r->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Acciones</div>
                <div class="card-body d-grid gap-2">

                    @if(in_array(auth()->user()->role, ['recepcionista','administrador']))
                        @if(in_array($cita->estado, ['pendiente', 'reprogramada']))
                            <a href="{{ route('pagos.create', $cita) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-credit-card me-1"></i> Registrar pago
                            </a>
                            @if($cita->pagos->contains('estado', 'confirmado'))
                                <form action="{{ route('citas.confirmar', $cita) }}" method="POST">
                                    @csrf @method('PUT')
                                    <button class="btn btn-success btn-sm w-100">
                                        <i class="bi bi-check2-circle me-1"></i> Confirmar cita
                                    </button>
                                </form>
                            @else
                                <div class="small text-muted">Para confirmar la cita primero valida un pago en el modulo de Pagos.</div>
                            @endif
                        @endif
                    @endif

                    @if(auth()->user()->role === 'psicologo' && $cita->psicologo_id === auth()->id() && !$cita->historialClinico && in_array($cita->estado, \App\Models\Cita::ESTADOS_ACTIVOS))
                        <a href="{{ route('psicologo.historial.create', $cita) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-journal-medical me-1"></i> Registrar historial / atender sesion
                        </a>
                    @endif

                    @if($cita->puedeReprogramarse())
                        <a href="{{ route('citas.reprogramar.form', $cita) }}" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-arrow-repeat me-1"></i> Reprogramar
                        </a>
                    @endif

                    @if($cita->puedeCancelarse())
                        <a href="{{ route('citas.cancelar.form', $cita) }}" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-x-circle me-1"></i> Cancelar cita
                        </a>
                    @endif
                </div>
            </div>

            @if($cita->pagos->isNotEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold">Pagos</div>
                    <ul class="list-group list-group-flush">
                        @foreach($cita->pagos as $pago)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $pago->metodo_pago)) }}</div>
                                    S/ {{ number_format($pago->monto, 2) }}
                                </div>
                                <span class="badge bg-{{ $pago->estado === 'confirmado' ? 'success' : ($pago->estado === 'rechazado' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($pago->estado) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection
