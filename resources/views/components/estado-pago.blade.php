@props(['estado'])

<x-badge :tono="$estado->tono()" {{ $attributes }}>{{ $estado->etiqueta() }}</x-badge>
