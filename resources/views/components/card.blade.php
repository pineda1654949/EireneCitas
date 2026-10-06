@props(['titulo' => null, 'descripcion' => null, 'padding' => true])

<section {{ $attributes->merge(['class' => 'card']) }}>
    @if ($titulo || isset($acciones))
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div>
                <h2 class="text-base font-semibold text-slate-900">{{ $titulo }}</h2>
                @if ($descripcion)
                    <p class="mt-0.5 text-sm text-slate-500">{{ $descripcion }}</p>
                @endif
            </div>
            @isset($acciones)
                <div class="flex items-center gap-2">{{ $acciones }}</div>
            @endisset
        </header>
    @endif

    <div @class(['px-5 py-5 sm:px-6' => $padding])>
        {{ $slot }}
    </div>
</section>
