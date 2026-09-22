@extends('layouts.panel')

@section('titulo', 'Nuevo psicologo')

@section('content')
    <div class="card border-0 shadow-sm" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.psicologos.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombres</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellidos</label>
                        <input type="text" name="apellidos" class="form-control" value="{{ old('apellidos') }}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">DNI</label>
                        <input type="text" name="dni" class="form-control" value="{{ old('dni') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telefono</label>
                        <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contrasena inicial</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Especialidades que atiende</label>
                    <div class="row">
                        @foreach($especialidades as $e)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="especialidades[]" value="{{ $e->id }}" id="esp{{ $e->id }}">
                                    <label class="form-check-label" for="esp{{ $e->id }}">{{ $e->nombre }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Guardar psicologo</button>
                <a href="{{ route('admin.psicologos.index') }}" class="btn btn-link">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
