{{-- Plantilla comun de las paginas de error. Uso: @include('errors.layout', [...]) --}}
<x-layouts.minimal :titulo="$titulo">
    <p class="mt-8 text-sm font-semibold text-brand-600">Error {{ $codigo }}</p>
    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ $titulo }}</h1>
    <p class="mt-3 text-base text-slate-600">{{ $mensaje }}</p>
    <div class="mt-8 flex justify-center gap-3">
        <a href="{{ url('/home') }}" class="btn btn-primary">Ir al inicio</a>
        @if ($volver ?? true)
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Volver</a>
        @endif
    </div>
</x-layouts.minimal>
