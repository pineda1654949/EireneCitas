@use('App\Enums\EstadoCita')

@php
    $usuario = auth()->user();
@endphp

<x-layouts.app :titulo="$usuario->esPersonalAdministrativo() ? 'Citas' : 'Mis citas'"
               subtitulo="Consulta, filtra y gestiona las citas registradas.">
    @can('create', App\Models\Cita::class)
        <x-slot:acciones>
            <a href="{{ route('citas.create') }}" class="btn btn-primary">
                <x-heroicon-o-plus class="size-5" /> {{ $usuario->esPaciente() ? 'Solicitar cita' : 'Registrar cita' }}
            </a>
        </x-slot:acciones>
    @endcan

    <x-card :padding="false">
        <form method="GET" action="{{ route('citas.index') }}" class="grid gap-4 border-b border-slate-100 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-5">
            @if ($usuario->esPersonalAdministrativo())
                <x-form.input name="buscar" label="Paciente" :value="$filtros['buscar'] ?? ''" placeholder="Nombre o DNI" class="lg:col-span-2" />
            @endif
            <x-form.select name="estado" label="Estado">
                <option value="">Todos</option>
                @foreach (EstadoCita::cases() as $caso)
                    <option value="{{ $caso->value }}" @selected(($filtros['estado'] ?? '') === $caso->value)>{{ $caso->etiqueta() }}</option>
                @endforeach
            </x-form.select>
            <x-form.input name="desde" label="Desde" type="date" :value="$filtros['desde'] ?? ''" />
            <x-form.input name="hasta" label="Hasta" type="date" :value="$filtros['hasta'] ?? ''" />
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-5">
                <button type="submit" class="btn btn-secondary"><x-heroicon-o-funnel class="size-5" /> Filtrar</button>
                @if (array_filter($filtros))
                    <a href="{{ route('citas.index') }}" class="btn btn-ghost">Limpiar filtros</a>
                @endif
            </div>
        </form>

        <x-tabla-citas :citas="$citas" :paciente="! $usuario->esPaciente()" :psicologo="! $usuario->esPsicologo()"
                       :especialidad="$usuario->esPaciente()" vacio="No se encontraron citas con esos filtros." />

        @if ($citas->hasPages())
            <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $citas->links() }}</div>
        @endif
    </x-card>
</x-layouts.app>
