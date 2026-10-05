@props(['usuario'])

<span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full text-xs font-semibold']) }} aria-hidden="true">
    {{ $usuario->iniciales }}
</span>
