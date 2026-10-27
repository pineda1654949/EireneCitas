<x-layouts.app titulo="Recepcionistas" subtitulo="Personal que registra citas, pacientes y pagos.">
    <x-slot:acciones>
        <a href="{{ route('admin.recepcionistas.create') }}" class="btn btn-primary">
            <x-heroicon-o-user-plus class="size-5" /> Nueva recepcionista
        </a>
    </x-slot:acciones>

    <x-card :padding="false">
        @if ($recepcionistas->isEmpty())
            <x-vacio icono="user-group" titulo="Aún no hay recepcionistas registradas" />
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th scope="col">Recepcionista</th>
                            <th scope="col">Teléfono</th>
                            <th scope="col">Estado</th>
                            <th scope="col"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recepcionistas as $recepcionista)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <x-avatar :usuario="$recepcionista" class="size-9 bg-sky-100 text-sky-700" />
                                        <div>
                                            <div class="font-medium text-slate-900">{{ $recepcionista->nombre_completo }}</div>
                                            <div class="text-xs text-slate-500">{{ $recepcionista->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $recepcionista->telefono ?? '—' }}</td>
                                <td>
                                    <x-badge :tono="$recepcionista->activo ? 'emerald' : 'slate'">
                                        {{ $recepcionista->activo ? 'Activa' : 'Inactiva' }}
                                    </x-badge>
                                </td>
                                <td class="whitespace-nowrap text-right">
                                    <a href="{{ route('admin.recepcionistas.edit', $recepcionista) }}" class="btn btn-ghost btn-sm">
                                        <x-heroicon-o-pencil-square class="size-4" /> Editar
                                    </a>
                                    <form method="POST" action="{{ route('admin.recepcionistas.destroy', $recepcionista) }}" class="inline" data-confirm="¿Eliminar a esta recepcionista? Si ya procesó pagos, solo se desactivará.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm text-rose-600 hover:bg-rose-50">
                                            <x-heroicon-o-trash class="size-4" /> Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($recepcionistas->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                    {{ $recepcionistas->links() }}
                </div>
            @endif
        @endif
    </x-card>
</x-layouts.app>