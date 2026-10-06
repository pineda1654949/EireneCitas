@props(['tono' => 'slate'])

@php
    // Clases completas (no concatenadas) para que Tailwind las detecte al compilar.
    $tonos = [
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
        'amber' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'sky' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'violet' => 'bg-violet-50 text-violet-700 ring-violet-600/20',
        'brand' => 'bg-brand-50 text-brand-700 ring-brand-600/20',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap ring-1 ring-inset '.($tonos[$tono] ?? $tonos['slate'])]) }}>
    {{ $slot }}
</span>
