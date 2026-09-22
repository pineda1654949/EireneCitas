@extends('layouts.panel')

@section('titulo', 'Editar paciente')

@section('content')
    <div class="card border-0 shadow-sm" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.pacientes.update', $paciente) }}">
                @csrf
                @method('PUT')
                @include('admin.pacientes._form')
                <button type="submit" class="btn btn-primary">Actualizar datos</button>
                <a href="{{ route('admin.pacientes.index') }}" class="btn btn-link">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
