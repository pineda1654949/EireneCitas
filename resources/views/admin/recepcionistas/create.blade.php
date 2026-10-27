<x-layouts.app titulo="Nueva recepcionista" subtitulo="Crea la cuenta con la que iniciará sesión.">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.recepcionistas.store') }}">
            @csrf
            @include('admin.recepcionistas._form')

            <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.recepcionistas.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar recepcionista</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>