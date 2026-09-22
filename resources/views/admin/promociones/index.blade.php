@extends('layouts.panel')

@section('titulo', 'Promociones')

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.promociones.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva promocion
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Nombre</th><th>Sesiones</th><th>Precio</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($promociones as $promo)
                        <tr>
                            <td>{{ $promo->nombre }}<div class="text-muted small">{{ $promo->descripcion }}</div></td>
                            <td>{{ $promo->numero_sesiones }}</td>
                            <td>S/ {{ number_format($promo->precio, 2) }}</td>
                            <td><span class="badge bg-{{ $promo->activa ? 'success' : 'secondary' }}">{{ $promo->activa ? 'Activa' : 'Inactiva' }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.promociones.edit', $promo) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                <form action="{{ route('admin.promociones.destroy', $promo) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta promocion?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay promociones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $promociones->links() }}</div>
    </div>
@endsection
