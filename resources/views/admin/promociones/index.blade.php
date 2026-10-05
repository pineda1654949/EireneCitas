<x-layouts.app titulo="Promociones" subtitulo="Paquetes de sesiones que ofrece la clínica.">
    <x-slot:acciones>
        <a href="{{ route('admin.promociones.create') }}" class="btn btn-primary"><x-heroicon-o-plus class="size-5" /> Nueva promoción</a>
    </x-slot:acciones>

    @if ($promociones->isEmpty())
        <x-card><x-vacio icono="tag" titulo="Aún no hay promociones" /></x-card>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($promociones as $promocion)
                <article class="card flex flex-col p-6">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="font-semibold text-slate-900">{{ $promocion->nombre }}</h2>
                        <x-badge :tono="$promocion->activa ? 'emerald' : 'slate'">{{ $promocion->activa ? 'Activa' : 'Inactiva' }}</x-badge>
                    </div>
                    <p class="mt-1 flex-1 text-sm text-slate-500">{{ $promocion->descripcion ?: 'Sin descripción.' }}</p>

                    <div class="mt-5 flex items-end justify-between">
                        <div>
                            <p class="text-2xl font-semibold text-slate-900 tabular-nums">S/ {{ number_format((float) $promocion->precio, 2) }}</p>
                            <p class="text-xs text-slate-500">{{ $promocion->numero_sesiones }} {{ $promocion->numero_sesiones === 1 ? 'sesión' : 'sesiones' }} · {{ $promocion->citas_count }} citas</p>
                        </div>
                        <div class="flex gap-1">
                            <a href="{{ route('admin.promociones.edit', $promocion) }}" class="btn btn-ghost btn-sm" aria-label="Editar {{ $promocion->nombre }}"><x-heroicon-o-pencil-square class="size-4" /></a>
                            <form method="POST" action="{{ route('admin.promociones.destroy', $promocion) }}" data-confirm="¿Eliminar la promoción «{{ $promocion->nombre }}»?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-rose-600 hover:bg-rose-50" aria-label="Eliminar {{ $promocion->nombre }}"><x-heroicon-o-trash class="size-4" /></button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($promociones->hasPages())
            <div class="mt-6">{{ $promociones->links() }}</div>
        @endif
    @endif
</x-layouts.app>
