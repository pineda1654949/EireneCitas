@php
    $etiquetasEvento = [
        'creado' => ['Creado', 'emerald'],
        'actualizado' => ['Actualizado', 'sky'],
        'eliminado' => ['Eliminado', 'rose'],
        'inicio_sesion' => ['Inicio de sesión', 'brand'],
        'cierre_sesion' => ['Cierre de sesión', 'slate'],
        'inicio_sesion_fallido' => ['Intento fallido', 'amber'],
        'contrasena_restablecida' => ['Contraseña restablecida', 'violet'],
    ];
    $formatear = fn ($valor) => is_array($valor) ? json_encode($valor, JSON_UNESCAPED_UNICODE) : (string) ($valor ?? '—');
@endphp

<x-layouts.app titulo="Auditoría" subtitulo="Registro de quién hizo qué y cuándo en el sistema.">
    <x-card :padding="false">
        <form method="GET" action="{{ route('admin.auditoria.index') }}" class="grid gap-4 border-b border-slate-100 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-5">
            <x-form.select name="evento" label="Evento">
                <option value="">Todos</option>
                @foreach ($eventos as $evento)
                    <option value="{{ $evento }}" @selected(($filtros['evento'] ?? '') === $evento)>{{ $etiquetasEvento[$evento][0] ?? $evento }}</option>
                @endforeach
            </x-form.select>
            <x-form.select name="tipo" label="Registro">
                <option value="">Todos</option>
                @foreach ($tipos as $tipo)
                    <option value="{{ $tipo }}" @selected(($filtros['tipo'] ?? '') === $tipo)>{{ $tipo === 'User' ? 'Usuario' : $tipo }}</option>
                @endforeach
            </x-form.select>
            <x-form.input name="desde" label="Desde" type="date" :value="$filtros['desde'] ?? ''" />
            <x-form.input name="hasta" label="Hasta" type="date" :value="$filtros['hasta'] ?? ''" />
            <div class="flex items-end">
                <button type="submit" class="btn btn-secondary w-full"><x-heroicon-o-funnel class="size-5" /> Filtrar</button>
            </div>
        </form>

        @if ($registros->isEmpty())
            <x-vacio icono="shield-check" titulo="No hay registros de auditoría con esos filtros" />
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($registros as $registro)
                    @php [$nombreEvento, $tono] = $etiquetasEvento[$registro->evento] ?? [$registro->evento, 'slate']; @endphp
                    <li class="px-5 py-4 sm:px-6">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                            <x-badge :tono="$tono">{{ $nombreEvento }}</x-badge>
                            <span class="font-medium text-slate-900">
                                {{ $registro->auditable_type ? $registro->tipoLegible().' #'.$registro->auditable_id : 'Sistema' }}
                            </span>
                            <span class="text-slate-500">por {{ $registro->usuario?->nombre_completo ?? 'Sistema / invitado' }}</span>
                            <span class="ml-auto text-xs text-slate-500 tabular-nums">{{ $registro->created_at?->format('d/m/Y H:i:s') }} · {{ $registro->ip ?? 'consola' }}</span>
                        </div>

                        @if ($registro->valores_nuevos || $registro->valores_anteriores)
                            <dl class="mt-2 grid gap-x-6 gap-y-1 rounded-lg bg-slate-50 p-3 text-xs sm:grid-cols-2">
                                @foreach (array_keys(($registro->valores_nuevos ?? []) + ($registro->valores_anteriores ?? [])) as $campo)
                                    <div class="flex gap-2">
                                        <dt class="shrink-0 font-medium text-slate-600">{{ $campo }}:</dt>
                                        <dd class="min-w-0 break-words text-slate-700">
                                            @if ($registro->evento === 'actualizado')
                                                <span class="text-rose-600 line-through">{{ $formatear($registro->valores_anteriores[$campo] ?? null) }}</span>
                                                → <span class="text-emerald-700">{{ $formatear($registro->valores_nuevos[$campo] ?? null) }}</span>
                                            @else
                                                {{ $formatear(($registro->valores_nuevos ?? $registro->valores_anteriores)[$campo] ?? null) }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </li>
                @endforeach
            </ul>

            @if ($registros->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $registros->links() }}</div>
            @endif
        @endif
    </x-card>
</x-layouts.app>
