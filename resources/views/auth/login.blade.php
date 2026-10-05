@extends('layouts.auth')

@section('titulo', 'Iniciar sesion')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Usuario o correo electronico</label>
            <input type="text" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Contrasena</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Recordarme</label>
        </div>
        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
    </form>

    <p class="text-center text-muted mt-4 mb-0">
        ¿Eres nuevo paciente? <a href="{{ route('register') }}">Registrate aqui</a>
    </p>

    <div class="alert alert-light border mt-4 small mb-0">
        <strong>Usuarios de prueba</strong> (contrasena: <code>contraseña</code>)<br>
        admin &middot; recepcion@eirene.test &middot; psicologo1@eirene.test &middot; paciente@eirene.test
    </div>
@endsection
