<x-layouts.app titulo="Derivaciones recibidas" subtitulo="Acepta o rechaza los pacientes que la clínica te deriva (RF-05).">
    @if ($derivaciones->isEmpty())
        <x-card>
            <x-vacio icono="inbox" titulo="No tienes derivaciones" descripcion="Cuando la clínica te derive un paciente, aparecerá aquí." />
        </x-card>
    @else
        <div class="space-y-4">
            @foreach ($derivaciones as $derivacion)
                <article class="card p-5 sm:p-6" data-derivacion="{{ $derivacion->id }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="font-semibold text-slate-900">{{ $derivacion->paciente->nombre_completo }}</h2>
                            <p class="text-sm text-slate-500">
                                {{ $derivacion->especialidad?->nombre ?? 'Sin especialidad' }}
                                · derivado el {{ $derivacion->created_at?->format('d/m/Y') }} por {{ $derivacion->derivadoPor?->name ?? 'la clínica' }}
                            </p>
                        </div>
                        <x-badge :tono="$derivacion->estado->tono()">{{ $derivacion->estado->etiqueta() }}</x-badge>
                    </div>

                    @if ($derivacion->paciente->motivo_consulta)
                        <p class="mt-3 text-sm text-slate-700"><span class="font-medium">Motivo de consulta:</span> {{ $derivacion->paciente->motivo_consulta }}</p>
                    @endif
                    @if ($derivacion->observaciones)
                        <p class="mt-1 text-sm text-slate-700"><span class="font-medium">Observaciones:</span> {{ $derivacion->observaciones }}</p>
                    @endif
                    @if ($derivacion->motivo_rechazo)
                        <p class="mt-1 text-sm text-rose-700"><span class="font-medium">Motivo del rechazo:</span> {{ $derivacion->motivo_rechazo }}</p>
                    @endif

                    @if ($derivacion->estaPendiente())
                        <div class="mt-5 grid gap-4 border-t border-slate-100 pt-5 sm:grid-cols-[auto_1fr] sm:items-start">
                            <form method="POST" action="{{ route('psicologo.derivaciones.aceptar', $derivacion) }}">
                                @csrf @method('PUT')
                                <button type="submit" class="btn btn-success"><x-heroicon-o-check-circle class="size-5" /> Aceptar</button>
                            </form>
                            <form method="POST" action="{{ route('psicologo.derivaciones.rechazar', $derivacion) }}" class="flex flex-col gap-2 sm:flex-row sm:items-start">
                                @csrf @method('PUT')
                                <label for="motivo-{{ $derivacion->id }}" class="sr-only">Motivo del rechazo</label>
                                <textarea id="motivo-{{ $derivacion->id }}" name="motivo_rechazo" rows="2" required minlength="10"
                                          placeholder="Motivo del rechazo (obligatorio, mínimo 10 caracteres)"
                                          class="form-control flex-1">{{ old('motivo_rechazo') }}</textarea>
                                <button type="submit" class="btn btn-secondary"><x-heroicon-o-x-circle class="size-5" /> Rechazar</button>
                            </form>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>

        @if ($derivaciones->hasPages())
            <div class="mt-6">{{ $derivaciones->links() }}</div>
        @endif
    @endif
</x-layouts.app>
