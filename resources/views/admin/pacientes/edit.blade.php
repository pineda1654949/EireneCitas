<x-layouts.app titulo="Ficha del paciente" :subtitulo="$paciente->nombre_completo">
    <div class="grid gap-6 lg:grid-cols-3">
        <x-card titulo="Datos personales" class="lg:col-span-2">
            <form method="POST" action="{{ route('admin.pacientes.update', $paciente) }}">
                @csrf
                @method('PUT')
                @include('admin.pacientes._form')

                <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ route('admin.pacientes.index') }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </x-card>

        <aside class="space-y-6">
            {{-- RF-05: derivacion del paciente a un psicologo --}}
            <x-card titulo="Derivación" descripcion="Asigna al paciente un psicólogo según sus síntomas.">
                <p class="mb-4 text-sm">
                    <span class="text-slate-500">Psicólogo asignado:</span>
                    <span class="font-medium text-slate-900">{{ $paciente->psicologoAsignado?->nombre_completo ?? 'Ninguno' }}</span>
                </p>

                <form method="POST" action="{{ route('admin.pacientes.derivar', $paciente) }}" class="space-y-4">
                    @csrf
                    <x-form.select name="psicologo_id" label="Derivar a" required>
                        <option value="">Selecciona un psicólogo...</option>
                        @foreach ($psicologos as $psicologo)
                            <option value="{{ $psicologo->id }}" @selected(old('psicologo_id') == $psicologo->id)>
                                {{ $psicologo->nombre_completo }} — {{ $psicologo->especialidades->pluck('nombre')->join(', ') ?: 'sin especialidad' }}
                            </option>
                        @endforeach
                    </x-form.select>
                    <x-form.select name="especialidad_id" label="Especialidad">
                        <option value="">Sin especificar</option>
                        @foreach ($especialidades as $especialidad)
                            <option value="{{ $especialidad->id }}" @selected(old('especialidad_id') == $especialidad->id)>{{ $especialidad->nombre }}</option>
                        @endforeach
                    </x-form.select>
                    <x-form.textarea name="observaciones" label="Observaciones para el psicólogo" rows="3" />
                    <button type="submit" class="btn btn-primary w-full"><x-heroicon-o-arrow-right-circle class="size-5" /> Derivar paciente</button>
                </form>

                @if ($paciente->derivaciones->isNotEmpty())
                    <ul class="mt-6 space-y-3 border-t border-slate-100 pt-4">
                        @foreach ($paciente->derivaciones as $derivacion)
                            <li class="text-sm">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-medium text-slate-900">{{ $derivacion->psicologo->nombre_completo }}</span>
                                    <x-badge :tono="$derivacion->estado->tono()">{{ $derivacion->estado->etiqueta() }}</x-badge>
                                </div>
                                <p class="text-xs text-slate-500">{{ $derivacion->created_at?->format('d/m/Y') }} · {{ $derivacion->especialidad?->nombre ?? 'Sin especialidad' }}</p>
                                @if ($derivacion->motivo_rechazo)
                                    <p class="mt-1 text-xs text-rose-700">Motivo: {{ $derivacion->motivo_rechazo }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </aside>
    </div>
</x-layouts.app>
