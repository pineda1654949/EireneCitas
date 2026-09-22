@extends('layouts.panel')

@section('titulo', 'Nueva promocion')

@section('content')
    <div class="card border-0 shadow-sm" style="max-width: 600px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.promociones.store') }}">
                @csrf
                @include('admin.promociones._form')
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('admin.promociones.index') }}" class="btn btn-link">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
