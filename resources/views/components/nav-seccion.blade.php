@props(['titulo'])

<div>
    <p class="px-3 pb-2 text-[11px] font-semibold tracking-wider text-slate-500 uppercase">{{ $titulo }}</p>
    <div class="space-y-1">{{ $slot }}</div>
</div>
