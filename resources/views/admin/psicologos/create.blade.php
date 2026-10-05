<x-layouts.app titulo="Nuevo psicólogo" subtitulo="Crea la cuenta del profesional y asigna sus especialidades.">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.psicologos.store') }}">
            @csrf
            @include('admin.psicologos._form')

            <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.psicologos.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar psicólogo</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>
