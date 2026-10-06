@props(['href', 'active' => false, 'icono'])

<a href="{{ $href }}" @if ($active) aria-current="page" @endif
   @class([
       'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
       'bg-brand-500/15 text-white' => $active,
       'text-slate-300 hover:bg-slate-800 hover:text-white' => ! $active,
   ])>
    <x-dynamic-component :component="'heroicon-o-'.$icono" @class(['size-5 shrink-0', 'text-brand-300' => $active, 'text-slate-500 group-hover:text-slate-300' => ! $active]) />
    {{ $slot }}
</a>
