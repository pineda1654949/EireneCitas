<x-layouts.app titulo="Cancelar cita" :subtitulo="'Cita #'.$cita->id">
    <x-card class="max-w-2xl">
        <div class="flex gap-4">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                <x-heroicon-o-exclamation-triangle class="size-6" />
            </span>
            <div>
                <h2 class="text-base font-semibold text-slate-900">¿Seguro que deseas cancelar esta cita?</h2>
                <p class="mt-1 text-sm text-slate-600">
                    Cita del <strong>{{ $cita->fecha->isoFormat('dddd D [de] MMMM') }}</strong> a las <strong>{{ $cita->hora_corta }} h</strong>.
                    Esta acción no se puede deshacer y el horario quedará libre para otros pacientes.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('citas.cancelar', $cita) }}" class="mt-6 space-y-5">
            @csrf
            @method('PUT')
            <x-form.textarea name="motivo" label="Motivo de la cancelación" rows="3" hint="Opcional, pero nos ayuda a mejorar." />

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('citas.show', $cita) }}" class="btn btn-ghost">Volver</a>
                <button type="submit" class="btn btn-danger"><x-heroicon-o-x-circle class="size-5" /> Sí, cancelar cita</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>
