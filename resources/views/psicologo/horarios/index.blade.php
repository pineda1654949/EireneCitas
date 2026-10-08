@use('App\Models\Horario')

<x-layouts.app titulo="Mi disponibilidad" subtitulo="Bloques semanales en los que los pacientes pueden reservar contigo (RF-02).">
    <div class="grid gap-6 lg:grid-cols-3">
        <x-card titulo="Agregar bloque" descripcion="Las sesiones duran {{ config('eirene.citas.duracion_minutos') }} min y se ofrecen cada {{ config('eirene.citas.intervalo_minutos') }} min.">
            <form method="POST" action="{{ route('psicologo.horarios.store') }}" class="space-y-5">
                @csrf
                <x-form.select name="dia_semana" label="Día" required>
                    @foreach (Horario::DIAS as $numero => $dia)
                        <option value="{{ $numero }}" @selected(old('dia_semana') == $numero)>{{ $dia }}</option>
                    @endforeach
                </x-form.select>
                <div class="grid grid-cols-2 gap-4">
                    <x-form.input name="hora_inicio" label="Desde" type="time" step="900" required />
                    <x-form.input name="hora_fin" label="Hasta" type="time" step="900" required />
                </div>
                <button type="submit" class="btn btn-primary w-full"><x-heroicon-o-plus class="size-5" /> Agregar bloque</button>
            </form>
        </x-card>

        <x-card titulo="Horario semanal" :padding="false" class="lg:col-span-2">
            @if ($horarios->isEmpty())
                <x-vacio icono="clock" titulo="Aún no configuraste tu disponibilidad"
                         descripcion="Mientras no agregues bloques, los pacientes no podrán reservar contigo." />
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($horarios as $dia => $bloques)
                        <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                            <span class="w-28 shrink-0 text-sm font-semibold text-slate-900">{{ Horario::DIAS[$dia] }}</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($bloques as $bloque)
                                    <div @class([
                                        'flex items-center gap-1 rounded-xl py-1 pr-1 pl-3 text-sm ring-1',
                                        'bg-brand-50 text-brand-800 ring-brand-200' => $bloque->activo,
                                        'bg-slate-50 text-slate-400 line-through ring-slate-200' => ! $bloque->activo,
                                    ])>
                                        <span class="tabular-nums">{{ substr($bloque->hora_inicio, 0, 5) }} – {{ substr($bloque->hora_fin, 0, 5) }}</span>
                                        <form method="POST" action="{{ route('psicologo.horarios.alternar', $bloque) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="rounded-lg p-1 text-slate-500 hover:bg-white hover:text-slate-800"
                                                    title="{{ $bloque->activo ? 'Pausar bloque' : 'Activar bloque' }}">
                                                <span class="sr-only">{{ $bloque->activo ? 'Pausar bloque' : 'Activar bloque' }}</span>
                                                @if ($bloque->activo)
                                                    <x-heroicon-m-pause class="size-4" />
                                                @else
                                                    <x-heroicon-m-play class="size-4" />
                                                @endif
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('psicologo.horarios.destroy', $bloque) }}" data-confirm="¿Eliminar este bloque de horario?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="rounded-lg p-1 text-slate-500 hover:bg-white hover:text-rose-600" aria-label="Eliminar bloque">
                                                <x-heroicon-m-x-mark class="size-4" />
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>
</x-layouts.app>
