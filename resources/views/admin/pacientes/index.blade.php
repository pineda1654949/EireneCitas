@extends('layouts.panel')

@section('titulo', 'Pacientes')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre o DNI..." value="{{ request('buscar') }}">
            <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
        </form>
        <a href="{{ route('admin.pacientes.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Nuevo paciente
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Nombre completo</th><th>DNI</th><th>Telefono</th><th>Correo</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($pacientes as $paciente)
                        <tr>
                            <td>{{ $paciente->nombre_completo }}</td>
                            <td>{{ $paciente->dni ?: '-' }}</td>
                            <td>{{ $paciente->telefono ?: '-' }}</td>
                            <td>{{ $paciente->correo ?: '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.pacientes.edit', $paciente) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                <form action="{{ route('admin.pacientes.destroy', $paciente) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este paciente?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay pacientes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $pacientes->links() }}</div>
    </div>
@endsection
