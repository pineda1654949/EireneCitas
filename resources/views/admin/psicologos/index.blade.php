@extends('layouts.panel')

@section('titulo', 'Psicologos')

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.psicologos.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Nuevo psicologo
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Nombre</th><th>Correo</th><th>Especialidades</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($psicologos as $psicologo)
                        <tr>
                            <td>{{ $psicologo->name }} {{ $psicologo->apellidos }}</td>
                            <td>{{ $psicologo->email }}</td>
                            <td>
                                @foreach($psicologo->especialidades as $e)
                                    <span class="badge bg-light text-dark border">{{ $e->nombre }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-{{ $psicologo->activo ? 'success' : 'secondary' }}">
                                    {{ $psicologo->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.psicologos.edit', $psicologo) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                <form action="{{ route('admin.psicologos.destroy', $psicologo) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este psicologo?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay psicologos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $psicologos->links() }}</div>
    </div>
@endsection
