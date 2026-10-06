<x-layouts.app titulo="Registrar sesión" :subtitulo="$cita->paciente->nombre_completo.' · '.$cita->fecha->format('d/m/Y').' '.$cita->hora_corta.' h'">
    <div class="grid gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2">
            <form method="POST" action="{{ route('psicologo.historial.store', $cita) }}" class="space-y-5">
                @csrf
                <x-form.textarea name="notas_sesion" label="Notas de la sesión" rows="10" required
                                 :value="$cita->historialClinico?->notas_sesion"
                                 hint="Se guardan cifradas y solo las ven los psicólogos que atienden al paciente." />

                <x-form.select name="avance" label="Avance del tratamiento">
                    <option value="">Sin especificar</option>
                    @foreach (['Inicial', 'En progreso', 'Estable', 'Alta'] as $avance)
                        <option value="{{ $avance }}" @selected(old('avance', $cita->historialClinico?->avance) === $avance)>{{ $avance }}</option>
                    @endforeach
                </x-form.select>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ route('citas.show', $cita) }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><x-heroicon-o-check-circle class="size-5" /> Guardar y marcar como atendida</button>
                </div>
            </form>
        </x-card>

        <aside>
            <x-card titulo="Paciente">
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-500">Nombre</dt><dd class="font-medium text-slate-900">{{ $cita->paciente->nombre_completo }}</dd></div>
                    @if ($cita->paciente->edad)
                        <div><dt class="text-slate-500">Edad</dt><dd class="font-medium text-slate-900">{{ $cita->paciente->edad }} años</dd></div>
                    @endif
                    <div><dt class="text-slate-500">Motivo de consulta</dt><dd class="text-slate-700">{{ $cita->motivo_consulta ?: ($cita->paciente->motivo_consulta ?: '—') }}</dd></div>
                </dl>
                <a href="{{ route('psicologo.historial.paciente', $cita->paciente) }}" class="btn btn-secondary mt-5 w-full">
                    <x-heroicon-o-folder-open class="size-5" /> Ver historia clínica
                </a>
            </x-card>
        </aside>
    </div>
</x-layouts.app>
