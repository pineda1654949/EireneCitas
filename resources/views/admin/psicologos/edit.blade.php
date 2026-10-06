<x-layouts.app titulo="Editar psicólogo" :subtitulo="$psicologo->nombre_completo">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.psicologos.update', $psicologo) }}">
            @csrf
            @method('PUT')
            @include('admin.psicologos._form')

            <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.psicologos.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>
