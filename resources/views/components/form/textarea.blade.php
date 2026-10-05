@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'rows' => 3])

@php
    $id = $attributes->get('id', $name);
    $tieneError = $errors->has($name);
@endphp

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }}
            @if ($attributes->get('required')) <span class="text-rose-500" aria-hidden="true">*</span> @endif
        </label>
    @endif

    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
              @if ($tieneError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
              {{ $attributes->except(['class', 'id'])->merge(['class' => 'form-control']) }}>{{ old($name, $value) }}</textarea>

    @if ($tieneError)
        <p id="{{ $id }}-error" class="form-error">{{ $errors->first($name) }}</p>
    @elseif ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
</div>
