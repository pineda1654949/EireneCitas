<x-layouts.app titulo="Psicólogos" subtitulo="Profesionales de la clínica y sus especialidades.">
    <x-slot:acciones>
        <a href="{{ route('admin.psicologos.create') }}" class="btn btn-primary"><x-heroicon-o-user-plus class="size-5" /> Nuevo psicólogo</a>
    </x-slot:acciones>

    <x-card :padding="false">
        @if ($psicologos->isEmpty())
            <x-vacio icono="identification" titulo="Aún no hay psicólogos registrados" />
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th scope="col">Psicólogo</th>
                            <th scope="col">Especialidades</th>
                            <th scope="col">Citas</th>
                            <th scope="col">Estado</th>
                            <th scope="col"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($psicologos as $psicologo)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <x-avatar :usuario="$psicologo" class="size-9 bg-violet-100 text-violet-700" />
                                        <div>
                                            <div class="font-medium text-slate-900">{{ $psicologo->nombre_completo }}</div>
                                            <div class="text-xs text-slate-500">{{ $psicologo->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($psicologo->especialidades as $especialidad)
                                            <x-badge tono="brand">{{ $especialidad->nombre }}</x-badge>
                                        @empty
                                            <span class="text-slate-400">Sin especialidades</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="tabular-nums">{{ $psicologo->citas_como_psicologo_count }}</td>
                                <td>
                                    <x-badge :tono="$psicologo->activo ? 'emerald' : 'slate'">{{ $psicologo->activo ? 'Activo' : 'Inactivo' }}</x-badge>
                                </td>
                                <td class="whitespace-nowrap text-right">
                                    <a href="{{ route('admin.psicologos.edit', $psicologo) }}" class="btn btn-ghost btn-sm"><x-heroicon-o-pencil-square class="size-4" /> Editar</a>
                                    <form method="POST" action="{{ route('admin.psicologos.destroy', $psicologo) }}" class="inline"
                                          data-confirm="{{ $psicologo->citas_como_psicologo_count ? 'El psicólogo tiene citas: se desactivará en lugar de eliminarse. ¿Continuar?' : '¿Eliminar a este psicólogo?' }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm text-rose-600 hover:bg-rose-50"><x-heroicon-o-trash class="size-4" /> Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($psicologos->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $psicologos->links() }}</div>
            @endif
        @endif
    </x-card>
</x-layouts.app>
