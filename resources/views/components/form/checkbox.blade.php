@props(['name', 'label', 'checked' => false, 'value' => '1', 'descripcion' => null])

<label {{ $attributes->only('class')->merge(['class' => 'flex items-start gap-3']) }}>
    {{-- Campo oculto para que un checkbox desmarcado envie "0". --}}
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}"
           @checked(old($name, $checked))
           {{ $attributes->except('class')->merge(['class' => 'mt-0.5 size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500']) }}>
    <span class="text-sm">
        <span class="font-medium text-slate-700">{{ $label }}</span>
        @if ($descripcion)
            <span class="block text-slate-500">{{ $descripcion }}</span>
        @endif
    </span>
</label>
