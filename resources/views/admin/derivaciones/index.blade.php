@use('App\Enums\EstadoDerivacion')

<x-layouts.app titulo="Derivaciones" subtitulo="Pacientes derivados a psicólogos y su respuesta (RF-05).">
    <x-card :padding="false">
        <nav class="flex gap-1 overflow-x-auto border-b border-slate-100 px-4 pt-3 sm:px-5" aria-label="Filtrar por estado">
            @foreach (['' => 'Todas'] + collect(EstadoDerivacion::cases())->mapWithKeys(fn ($e) => [$e->value => $e->etiqueta().'s'])->all() as $valor => $nombre)
                @php $activo = ($filtros['estado'] ?? '') === $valor; @endphp
                <a href="{{ route('admin.derivaciones.index', array_filter(['estado' => $valor])) }}"
                   @class(['border-b-2 px-3 pb-3 text-sm font-medium whitespace-nowrap',
                           'border-brand-600 text-brand-700' => $activo,
                           'border-transparent text-slate-500 hover:text-slate-700' => ! $activo])>
                    {{ $nombre }}
                </a>
            @endforeach
        </nav>

        @if ($derivaciones->isEmpty())
            <x-vacio icono="arrow-right-circle" titulo="No hay derivaciones registradas"
                     descripcion="Deriva a un paciente desde su ficha, en el módulo Pacientes." />
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th scope="col">Fecha</th>
                            <th scope="col">Paciente</th>
                            <th scope="col">Psicólogo</th>
                            <th scope="col">Especialidad</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($derivaciones as $derivacion)
                            <tr>
                                <td class="whitespace-nowrap">
                                    <div>{{ $derivacion->created_at?->format('d/m/Y') }}</div>
                                    <div class="text-xs text-slate-500">por {{ $derivacion->derivadoPor?->name ?? '—' }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.pacientes.edit', $derivacion->paciente) }}" class="font-medium text-slate-900 hover:text-brand-700">
                                        {{ $derivacion->paciente->nombre_completo }}
                                    </a>
                                </td>
                                <td>{{ $derivacion->psicologo->nombre_completo }}</td>
                                <td>{{ $derivacion->especialidad?->nombre ?? '—' }}</td>
                                <td>
                                    <x-badge :tono="$derivacion->estado->tono()">{{ $derivacion->estado->etiqueta() }}</x-badge>
                                    @if ($derivacion->motivo_rechazo)
                                        <div class="mt-1 max-w-xs text-xs text-rose-700">{{ $derivacion->motivo_rechazo }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($derivaciones->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $derivaciones->links() }}</div>
            @endif
        @endif
    </x-card>
</x-layouts.app>
