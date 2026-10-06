<x-layouts.app titulo="Nueva promoción">
    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.promociones.store') }}">
            @csrf
            @include('admin.promociones._form')

            <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.promociones.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear promoción</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>
