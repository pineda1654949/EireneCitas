@props(['icono' => 'inbox', 'titulo', 'descripcion' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-14 text-center']) }}>
    <span class="flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
        <x-dynamic-component :component="'heroicon-o-'.$icono" class="size-6" />
    </span>
    <h3 class="mt-4 text-sm font-semibold text-slate-900">{{ $titulo }}</h3>
    @if ($descripcion)
        <p class="mt-1 max-w-sm text-sm text-slate-500">{{ $descripcion }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
