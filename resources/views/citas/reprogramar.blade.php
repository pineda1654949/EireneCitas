<x-layouts.app titulo="Reprogramar cita" :subtitulo="'Cita #'.$cita->id.' con '.$cita->psicologo->nombre_completo">
    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('citas.reprogramar', $cita) }}" class="lg:col-span-2" data-reserva
              data-url-agenda="{{ route('api.agenda-del-dia') }}" data-cita-id="{{ $cita->id }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="psicologo_id" value="{{ $cita->psicologo_id }}" data-psicologo>

            <x-card titulo="Nueva fecha y hora">
                <div class="space-y-5">
                    <x-form.input name="fecha" label="Nueva fecha" type="date" required data-fecha
                                  min="{{ today()->toDateString() }}" class="sm:max-w-xs" />

                    <fieldset>
                        <legend class="form-label">Nueva hora <span class="text-rose-500" aria-hidden="true">*</span></legend>
                        <p class="text-sm text-slate-500" data-horas-ayuda>Elige una fecha para ver la agenda del psicólogo.</p>
                        <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-5" data-horas data-inicial="{{ old('hora') }}" aria-live="polite"></div>
                        <x-leyenda-horas />
                        @error('hora') <p class="form-error">{{ $message }}</p> @enderror
                    </fieldset>

                    <x-form.textarea name="motivo" label="Motivo de la reprogramación" rows="3" required minlength="5"
                                     hint="Queda registrado en el historial de la cita (RN-01)." />
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ route('citas.show', $cita) }}" class="btn btn-ghost">Volver</a>
                    <button type="submit" class="btn btn-primary"><x-heroicon-o-arrow-path class="size-5" /> Reprogramar</button>
                </div>
            </x-card>
        </form>

        <aside>
            <x-card titulo="Cita actual">
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-500">Fecha</dt><dd class="font-medium text-slate-900">{{ ucfirst($cita->fecha->isoFormat('dddd D [de] MMMM')) }}</dd></div>
                    <div><dt class="text-slate-500">Hora</dt><dd class="font-medium text-slate-900">{{ $cita->hora_corta }} h</dd></div>
                    <div><dt class="text-slate-500">Reprogramaciones disponibles</dt><dd class="font-medium text-slate-900">{{ $cita->reprogramacionesRestantes() }} de {{ App\Models\Cita::maxReprogramaciones() }}</dd></div>
                </dl>
                @unless ($cita->puedeReprogramarse())
                    <p class="mt-4 rounded-lg bg-amber-50 p-3 text-xs text-amber-800 ring-1 ring-amber-600/20">
                        Esta cita ya usó sus {{ App\Models\Cita::maxReprogramaciones() }} reprogramaciones. Como administrador, estás autorizando un cambio adicional (RN-01).
                    </p>
                @endunless
            </x-card>
        </aside>
    </div>
</x-layouts.app>
