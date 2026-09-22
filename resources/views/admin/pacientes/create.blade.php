@extends('layouts.panel')

@section('titulo', 'Nuevo paciente')

@section('content')
    <div class="card border-0 shadow-sm" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.pacientes.store') }}">
                @csrf
                @include('admin.pacientes._form')
                <button type="submit" class="btn btn-primary">Guardar paciente</button>
                <a href="{{ route('admin.pacientes.index') }}" class="btn btn-link">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
