<x-layouts.app titulo="Pacientes" subtitulo="Fichas de los pacientes de la clínica.">
    <x-slot:acciones>
        <a href="{{ route('admin.pacientes.create') }}" class="btn btn-primary"><x-heroicon-o-user-plus class="size-5" /> Nuevo paciente</a>
    </x-slot:acciones>

    <x-card :padding="false">
        <form method="GET" action="{{ route('admin.pacientes.index') }}" class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:p-6">
            <label for="buscar" class="sr-only">Buscar paciente</label>
            <div class="relative flex-1">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-slate-400" />
                <input id="buscar" type="search" name="buscar" value="{{ $buscar }}" placeholder="Buscar por nombre, apellido, DNI o correo" class="form-control pl-11">
            </div>
            <button type="submit" class="btn btn-secondary">Buscar</button>
        </form>

        @if ($pacientes->isEmpty())
            <x-vacio icono="users" titulo="No se encontraron pacientes" :descripcion="$buscar ? 'Prueba con otro término de búsqueda.' : 'Registra el primer paciente de la clínica.'" />
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th scope="col">Paciente</th>
                            <th scope="col">DNI</th>
                            <th scope="col">Contacto</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Citas</th>
                            <th scope="col"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($pacientes as $paciente)
                            <tr>
                                <td>
                                    <div class="font-medium text-slate-900">{{ $paciente->apellidos }}, {{ $paciente->nombres }}</div>
                                    <div class="text-xs text-slate-500">
                                        @if ($paciente->edad) {{ $paciente->edad }} años · @endif
                                        {{ $paciente->user_id ? 'Con cuenta web' : 'Sin cuenta web' }}
                                    </div>
                                </td>
                                <td class="tabular-nums">{{ $paciente->dni ?? '—' }}</td>
                                <td>
                                    <div>{{ $paciente->telefono ?? '—' }}</div>
                                    <div class="text-xs text-slate-500">{{ $paciente->correo }}</div>
                                </td>
                                <td>
                                    <x-badge :tono="$paciente->estado_atencion->tono()">{{ $paciente->estado_atencion->etiqueta() }}</x-badge>
                                    @if ($paciente->psicologoAsignado)
                                        <div class="mt-1 text-xs text-slate-500">{{ $paciente->psicologoAsignado->nombre_completo }}</div>
                                    @endif
                                </td>
                                <td><x-badge>{{ $paciente->citas_count }}</x-badge></td>
                                <td class="whitespace-nowrap text-right">
                                    <a href="{{ route('admin.pacientes.edit', $paciente) }}" class="btn btn-ghost btn-sm"><x-heroicon-o-pencil-square class="size-4" /> Ficha</a>
                                    @if ($paciente->citas_count === 0)
                                        <form method="POST" action="{{ route('admin.pacientes.destroy', $paciente) }}" class="inline" data-confirm="¿Eliminar la ficha de este paciente?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm text-rose-600 hover:bg-rose-50"><x-heroicon-o-trash class="size-4" /> Eliminar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($pacientes->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $pacientes->links() }}</div>
            @endif
        @endif
    </x-card>
</x-layouts.app>
