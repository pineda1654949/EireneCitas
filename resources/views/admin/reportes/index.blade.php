@use('App\Enums\EstadoCita')

@php
    $cantidad = fn (EstadoCita $e) => (int) ($citasPorEstado[$e->value] ?? 0);
    $tasaAsistencia = $totalCitas ? round($cantidad(EstadoCita::Atendida) / $totalCitas * 100) : 0;
    $maximoPsicologo = max(1, (int) $citasPorPsicologo->max('total'));
@endphp

<x-layouts.app titulo="Reportes" subtitulo="Indicadores de atención de la clínica (RF-08).">
    <x-slot:acciones>
        <a href="{{ route('reportes.exportar', ['desde' => $desde, 'hasta' => $hasta]) }}" class="btn btn-secondary">
            <x-heroicon-o-arrow-down-tray class="size-5" /> Exportar CSV
        </a>
    </x-slot:acciones>

    <x-card class="mb-6">
        <form method="GET" action="{{ route('reportes.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <x-form.input name="desde" label="Desde" type="date" :value="$desde" />
            <x-form.input name="hasta" label="Hasta" type="date" :value="$hasta" />
            <button type="submit" class="btn btn-primary"><x-heroicon-o-funnel class="size-5" /> Aplicar</button>
        </form>
    </x-card>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat etiqueta="Citas del periodo" :valor="$totalCitas" icono="calendar-days" />
        <x-stat etiqueta="Tasa de atención" :valor="$tasaAsistencia.'%'" icono="check-badge" tono="emerald" detalle="Citas atendidas / total" />
        <x-stat etiqueta="Reprogramaciones" :valor="$totalReprogramaciones" icono="arrow-path" tono="violet" />
        <x-stat etiqueta="Ingresos confirmados" :valor="'S/ '.number_format((float) $ingresosConfirmados, 2)" icono="banknotes" tono="amber" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-card titulo="Citas por estado">
            <ul class="space-y-4">
                @foreach (EstadoCita::cases() as $caso)
                    @php $n = $cantidad($caso); @endphp
                    <li>
                        <div class="mb-1.5 flex items-center justify-between text-sm">
                            <x-estado-cita :estado="$caso" />
                            <span class="text-slate-500 tabular-nums"><strong class="text-slate-900">{{ $n }}</strong> · {{ $totalCitas ? round($n / $totalCitas * 100) : 0 }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-brand-500" style="width: {{ $totalCitas ? round($n / $totalCitas * 100) : 0 }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-card>

        <x-card titulo="Citas por psicólogo" :padding="false">
            @if ($citasPorPsicologo->isEmpty())
                <x-vacio icono="chart-bar" titulo="Sin citas en el periodo" />
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($citasPorPsicologo as $fila)
                        <li class="px-5 py-4 sm:px-6">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-900">{{ $fila->name }} {{ $fila->apellidos }}</span>
                                <span class="text-slate-500 tabular-nums"><strong class="text-slate-900">{{ $fila->total }}</strong> citas · {{ $fila->atendidas }} atendidas</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-violet-500" style="width: {{ round($fila->total / $maximoPsicologo * 100) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>
</x-layouts.app>
