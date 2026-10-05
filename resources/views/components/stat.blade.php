@props(['etiqueta', 'valor', 'icono', 'tono' => 'brand', 'detalle' => null, 'href' => null])

@php
    $tonos = [
        'brand' => 'bg-brand-50 text-brand-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'sky' => 'bg-sky-50 text-sky-600',
        'rose' => 'bg-rose-50 text-rose-600',
        'violet' => 'bg-violet-50 text-violet-600',
    ];
    $clases = 'card flex items-start gap-4 p-5 transition'.($href ? ' hover:ring-brand-300' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $clases]) }}>
@else
    <div {{ $attributes->merge(['class' => $clases]) }}>
@endif
    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $tonos[$tono] ?? $tonos['brand'] }}">
        <x-dynamic-component :component="'heroicon-o-'.$icono" class="size-6" />
    </span>
    <div class="min-w-0">
        <p class="text-sm text-slate-500">{{ $etiqueta }}</p>
        <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{{ $valor }}</p>
        @if ($detalle)
            <p class="mt-1 text-xs text-slate-500">{{ $detalle }}</p>
        @endif
    </div>
@if ($href)
    </a>
@else
    </div>
@endif
