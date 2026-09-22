@extends('layouts.auth')

@section('titulo', 'Crear cuenta')

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nombres</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
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
            <label class="form-label">Correo electronico</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Contrasena</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Confirmar contrasena</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Crear cuenta</button>
    </form>

    <p class="text-center text-muted mt-4 mb-0">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesion</a>
    </p>
@endsection
