<x-layouts.app :titulo="$pacientes ? 'Registrar cita' : 'Solicitar cita'"
               subtitulo="Elige la especialidad, el psicólogo y un horario disponible.">
    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('citas.store') }}" class="space-y-6 lg:col-span-2" data-reserva
              data-url-psicologos="{{ route('api.especialidades.psicologos', ['especialidad' => '__ID__']) }}"
              data-url-horas="{{ route('api.horas-disponibles') }}">
            @csrf

            @if ($pacientes)
                <x-card titulo="1. Paciente">
                    <x-form.select name="paciente_id" label="Paciente" required
                                   hint="¿Es un paciente nuevo? Regístralo primero en el módulo Pacientes.">
                        <option value="">Selecciona el paciente...</option>
                        @foreach ($pacientes as $p)
                            <option value="{{ $p->id }}" @selected(old('paciente_id') == $p->id)>
                                {{ $p->apellidos }}, {{ $p->nombres }} @if ($p->dni) — DNI {{ $p->dni }} @endif
                            </option>
                        @endforeach
                    </x-form.select>
                </x-card>
            @endif

            <x-card :titulo="($pacientes ? '2' : '1').'. Profesional'">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.select name="especialidad_id" label="Motivo de consulta / especialidad" required data-especialidad>
                        <option value="">Selecciona una opción...</option>
                        @foreach ($especialidades as $e)
                            <option value="{{ $e->id }}" @selected(old('especialidad_id') == $e->id)>{{ $e->nombre }}</option>
                        @endforeach
                    </x-form.select>

                    <x-form.select name="psicologo_id" label="Psicólogo" required disabled data-psicologo data-inicial="{{ old('psicologo_id') }}">
                        <option value="">Primero elige una especialidad...</option>
                    </x-form.select>
                </div>
            </x-card>

            <x-card :titulo="($pacientes ? '3' : '2').'. Fecha y hora'">
                <x-form.input name="fecha" label="Fecha" type="date" required data-fecha
                              min="{{ today()->toDateString() }}" class="sm:max-w-xs" />

                <fieldset class="mt-5">
                    <legend class="form-label">Horas disponibles <span class="text-rose-500" aria-hidden="true">*</span></legend>
                    <p class="text-sm text-slate-500" data-horas-ayuda>Elige psicólogo y fecha para ver las horas libres.</p>
                    <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-5" data-horas data-inicial="{{ old('hora') }}" aria-live="polite"></div>
                    @error('hora') <p class="form-error">{{ $message }}</p> @enderror
                </fieldset>
            </x-card>

            <x-card :titulo="($pacientes ? '4' : '3').'. Detalles'">
                <div class="space-y-5">
                    <x-form.select name="promocion_id" label="Promoción">
                        <option value="">Sesión individual (sin promoción)</option>
                        @foreach ($promociones as $promo)
                            <option value="{{ $promo->id }}" @selected(old('promocion_id') == $promo->id)>
                                {{ $promo->nombre }} — {{ $promo->numero_sesiones }} {{ $promo->numero_sesiones === 1 ? 'sesión' : 'sesiones' }} — S/ {{ number_format((float) $promo->precio, 2) }}
                            </option>
                        @endforeach
                    </x-form.select>

                    <x-form.textarea name="motivo_consulta" label="Cuéntanos brevemente el motivo de la consulta" rows="4"
                                     hint="Opcional. Solo lo verá el personal de la clínica y tu psicólogo." />
                </div>
            </x-card>

            <div class="flex justify-end gap-3">
                <a href="{{ route('citas.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary"><x-heroicon-o-check-circle class="size-5" /> Registrar cita</button>
            </div>
        </form>

        <aside class="space-y-4">
            <x-card titulo="¿Cómo funciona?">
                <ol class="space-y-4 text-sm text-slate-600">
                    <li class="flex gap-3"><span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700">1</span> Eliges el horario: solo se muestran las horas realmente libres.</li>
                    <li class="flex gap-3"><span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700">2</span> La cita queda <strong>pendiente</strong> hasta que la clínica valide el pago.</li>
                    <li class="flex gap-3"><span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700">3</span> Recibirás un correo con la confirmación y un recordatorio el día anterior.</li>
                </ol>
            </x-card>
            <p class="rounded-xl bg-slate-100 p-4 text-xs text-slate-600">
                Puedes reprogramar una cita hasta {{ App\Models\Cita::maxReprogramaciones() }} veces. Cada sesión dura {{ config('eirene.citas.duracion_minutos') }} minutos.
            </p>
        </aside>
    </div>
</x-layouts.app>
