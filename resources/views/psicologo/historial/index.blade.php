<x-layouts.app titulo="Historia clínica" :subtitulo="$paciente->nombre_completo">
    <x-slot:acciones>
        <a href="{{ url()->previous() }}" class="btn btn-ghost"><x-heroicon-m-arrow-left class="size-4" /> Volver</a>
    </x-slot:acciones>

    <div class="grid gap-6 lg:grid-cols-3">
        <aside class="lg:order-last">
            <x-card titulo="Datos del paciente">
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-500">DNI</dt><dd class="font-medium text-slate-900">{{ $paciente->dni ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Edad</dt><dd class="font-medium text-slate-900">{{ $paciente->edad ? $paciente->edad.' años' : '—' }}</dd></div>
                    <div><dt class="text-slate-500">Teléfono</dt><dd class="font-medium text-slate-900">{{ $paciente->telefono ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Motivo de consulta inicial</dt><dd class="text-slate-700">{{ $paciente->motivo_consulta ?: '—' }}</dd></div>
                </dl>
            </x-card>
        </aside>

        <div class="lg:col-span-2">
            <x-card titulo="Sesiones registradas" :descripcion="$historiales->count().' '.($historiales->count() === 1 ? 'sesión' : 'sesiones')">
                @if ($historiales->isEmpty())
                    <x-vacio icono="document-text" titulo="Sin sesiones registradas" descripcion="Las notas aparecerán aquí cuando se atienda una sesión." />
                @else
                    <ol class="relative space-y-8 border-l border-slate-200 pl-6">
                        @foreach ($historiales as $historial)
                            <li class="relative">
                                <span class="absolute -left-[31px] size-4 rounded-full bg-brand-500 ring-4 ring-white"></span>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-semibold text-slate-900">{{ $historial->cita->fecha->isoFormat('D [de] MMMM [de] YYYY') }}</p>
                                    @if ($historial->avance) <x-badge tono="sky">{{ $historial->avance }}</x-badge> @endif
                                </div>
                                <p class="text-xs text-slate-500">Atendió: {{ $historial->psicologo->nombre_completo }}</p>
                                <p class="mt-2 text-sm whitespace-pre-line text-slate-700">{{ $historial->notas_sesion }}</p>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-card>
        </div>
    </div>
</x-layouts.app>
