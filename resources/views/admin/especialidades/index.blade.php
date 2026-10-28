<x-layouts.app titulo="Especialidades" subtitulo="Áreas de atención que ofrece la clínica.">
    <x-slot:acciones>
        <a href="{{ route('admin.especialidades.create') }}" class="btn btn-primary"><x-heroicon-o-plus class="size-5" /> Nueva especialidad</a>
    </x-slot:acciones>

    <x-card :padding="false">
        @if ($especialidades->isEmpty())
            <x-vacio icono="academic-cap" titulo="Aún no hay especialidades registradas" />
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th scope="col">Especialidad</th>
                            <th scope="col">Psicólogos</th>
                            <th scope="col">Citas</th>
                            <th scope="col"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($especialidades as $especialidad)
                            <tr>
                                <td>
                                    <div class="font-medium text-slate-900">{{ $especialidad->nombre }}</div>
                                    <div class="text-xs text-slate-500">{{ $especialidad->descripcion ?? 'Sin descripción' }}</div>
                                </td>
                                <td class="tabular-nums">{{ $especialidad->psicologos_count }}</td>
                                <td class="tabular-nums">{{ $especialidad->citas_count }}</td>
                                <td class="whitespace-nowrap text-right">
                                    <a href="{{ route('admin.especialidades.edit', $especialidad) }}" class="btn btn-ghost btn-sm"><x-heroicon-o-pencil-square class="size-4" /> Editar</a>
                                    <form method="POST" action="{{ route('admin.especialidades.destroy', $especialidad) }}" class="inline"
                                          data-confirm="¿Eliminar la especialidad «{{ $especialidad->nombre }}»?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm text-rose-600 hover:bg-rose-50"><x-heroicon-o-trash class="size-4" /> Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($especialidades->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $especialidades->links() }}</div>
            @endif
        @endif
    </x-card>
</x-layouts.app>