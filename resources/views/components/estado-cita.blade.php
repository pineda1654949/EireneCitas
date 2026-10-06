@props(['estado'])

<x-badge :tono="$estado->tono()" {{ $attributes }}>
    <span class="size-1.5 rounded-full bg-current opacity-70"></span>
    {{ $estado->etiqueta() }}
</x-badge>
