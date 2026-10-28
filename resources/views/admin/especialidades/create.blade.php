<x-layouts.app titulo="Nueva especialidad" subtitulo="Agrega un área de atención para asignarla a los psicólogos.">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.especialidades.store') }}">
            @csrf
            @include('admin.especialidades._form')

            <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.especialidades.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar especialidad</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>
