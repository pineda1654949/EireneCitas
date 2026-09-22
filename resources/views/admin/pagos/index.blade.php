@extends('layouts.panel')

@section('titulo', 'Control de pagos')

@section('content')
    <form method="GET" class="mb-3 d-flex gap-2">
        <select name="estado" class="form-select" style="max-width: 220px;" onchange="this.form.submit()">
            <option value="">Todos los estados</option>
            <option value="pendiente" @selected(request('estado')==='pendiente')>Pendiente</option>
            <option value="confirmado" @selected(request('estado')==='confirmado')>Confirmado</option>
            <option value="rechazado" @selected(request('estado')==='rechazado')>Rechazado</option>
        </select>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Cita</th><th>Paciente</th><th>Monto</th><th>Metodo</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($pagos as $pago)
                        <tr>
                            <td><a href="{{ route('citas.show', $pago->cita) }}">#{{ $pago->cita_id }}</a></td>
                            <td>{{ $pago->cita->paciente->nombre_completo }}</td>
                            <td>S/ {{ number_format($pago->monto, 2) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $pago->metodo_pago)) }}</td>
                            <td>
                                <span class="badge bg-{{ $pago->estado === 'confirmado' ? 'success' : ($pago->estado === 'rechazado' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($pago->estado) }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($pago->estado === 'pendiente')
                                    <form action="{{ route('pagos.validar', $pago) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <button class="btn btn-sm btn-outline-success">Validar</button>
                                    </form>
                                    <form action="{{ route('pagos.rechazar', $pago) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <button class="btn btn-sm btn-outline-danger">Rechazar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No hay pagos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $pagos->links() }}</div>
    </div>
@endsection
