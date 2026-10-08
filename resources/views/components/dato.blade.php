@props(['etiqueta'])

{{-- Cada dato es su propia lista <dl>: el componente es HTML valido por si solo. --}}
<dl {{ $attributes->merge(['class' => 'py-3 sm:grid sm:grid-cols-3 sm:gap-4']) }}>
    <dt class="text-sm text-slate-500">{{ $etiqueta }}</dt>
    <dd class="mt-1 text-sm font-medium text-slate-900 sm:col-span-2 sm:mt-0">{{ $slot }}</dd>
</dl>
