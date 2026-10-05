<x-layouts.app titulo="Nuevo paciente" subtitulo="Registra la ficha de un paciente atendido en persona o por teléfono.">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.pacientes.store') }}">
            @csrf
            @include('admin.pacientes._form')

            <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.pacientes.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar paciente</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>
