@use('App\Enums\EstadoCita')

@php
    $estado = fn (EstadoCita $e) => (int) ($citasPorEstado[$e->value] ?? 0);
    $total = max(1, (int) $citasPorEstado->sum());
@endphp

<x-layouts.app titulo="Panel de administración" subtitulo="Resumen general de la actividad de la clínica.">
    <x-slot:acciones>
        <a href="{{ route('reportes.index') }}" class="btn btn-secondary"><x-heroicon-o-chart-bar class="size-5" /> Reportes</a>
        <a href="{{ route('citas.create') }}" class="btn btn-primary"><x-heroicon-o-plus class="size-5" /> Registrar cita</a>
    </x-slot:acciones>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat etiqueta="Pacientes registrados" :valor="number_format($totalPacientes)" icono="users" :href="route('admin.pacientes.index')" />
        <x-stat etiqueta="Psicólogos activos" :valor="$totalPsicologos" icono="identification" tono="violet" :href="route('admin.psicologos.index')" />
        <x-stat etiqueta="Citas pendientes" :valor="$estado(EstadoCita::Pendiente)" icono="clock" tono="amber"
                detalle="Esperan validación de pago" :href="route('citas.index', ['estado' => 'pendiente'])" />
        <x-stat etiqueta="Ingresos del mes" :valor="'S/ '.number_format((float) $ingresosMes, 2)" icono="banknotes" tono="emerald"
                detalle="Pagos validados" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-card titulo="Citas por estado" descripcion="Histórico total" class="lg:col-span-1">
            <ul class="space-y-4">
                @foreach (EstadoCita::cases() as $caso)
                    @php $cantidad = $estado($caso); @endphp
                    <li>
                        <div class="mb-1.5 flex items-center justify-between text-sm">
                            <x-estado-cita :estado="$caso" />
                            <span class="font-semibold text-slate-900 tabular-nums">{{ $cantidad }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-brand-500" style="width: {{ round($cantidad / $total * 100) }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-card>

        <x-card titulo="Últimas citas registradas" :padding="false" class="lg:col-span-2">
            <x-slot:acciones>
                <a href="{{ route('citas.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Ver todas</a>
            </x-slot:acciones>
            <x-tabla-citas :citas="$ultimasCitas" vacio="Aún no se registraron citas." />
        </x-card>
    </div>
</x-layouts.app>
