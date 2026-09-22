@extends('layouts.panel')

@section('titulo', 'Editar promocion')

@section('content')
    <div class="card border-0 shadow-sm" style="max-width: 600px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.promociones.update', $promocion) }}">
                @csrf
                @method('PUT')
                @include('admin.promociones._form')
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('admin.promociones.index') }}" class="btn btn-link">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
