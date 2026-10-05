{{-- Mensajes flash de exito y resumen de errores de validacion. --}}
@if (session('status'))
    <div class="mb-6 flex items-start gap-3 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 ring-1 ring-emerald-600/20 transition-opacity duration-300"
         role="status" data-alert data-autoclose>
        <x-heroicon-s-check-circle class="size-5 shrink-0 text-emerald-500" />
        <p class="flex-1">{{ session('status') }}</p>
        <button type="button" class="text-emerald-600 hover:text-emerald-800" data-dismiss aria-label="Cerrar">
            <x-heroicon-m-x-mark class="size-5" />
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 flex items-start gap-3 rounded-xl bg-rose-50 p-4 text-sm text-rose-800 ring-1 ring-rose-600/20" role="alert" data-alert>
        <x-heroicon-s-exclamation-circle class="size-5 shrink-0 text-rose-500" />
        <div class="flex-1">
            @if ($errors->count() === 1)
                <p>{{ $errors->first() }}</p>
            @else
                <p class="font-medium">Revisa los siguientes datos:</p>
                <ul class="mt-1 list-disc space-y-0.5 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
        <button type="button" class="text-rose-600 hover:text-rose-800" data-dismiss aria-label="Cerrar">
            <x-heroicon-m-x-mark class="size-5" />
        </button>
    </div>
@endif
