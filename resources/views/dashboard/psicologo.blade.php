<x-layouts.app :titulo="'Hola, '.auth()->user()->name" subtitulo="Tu agenda de sesiones.">
    <x-slot:acciones>
        <a href="{{ route('psicologo.horarios.index') }}" class="btn btn-secondary"><x-heroicon-o-clock class="size-5" /> Mi disponibilidad</a>
    </x-slot:acciones>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat etiqueta="Sesiones de hoy" :valor="$citasHoy->count()" icono="calendar-days" />
        <x-stat etiqueta="Próximas sesiones" :valor="$proximasCitas->count()" icono="arrow-trending-up" tono="violet" />
        <x-stat etiqueta="Atendidas este mes" :valor="$atendidasMes" icono="check-badge" tono="emerald" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-card titulo="Sesiones de hoy" :padding="false">
            @forelse ($citasHoy as $cita)
                <a href="{{ route('citas.show', $cita) }}" class="flex items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-0 hover:bg-slate-50 sm:px-6">
                    <span class="flex w-14 shrink-0 flex-col items-center rounded-xl bg-brand-50 py-2 text-brand-700">
                        <span class="text-sm font-semibold tabular-nums">{{ $cita->hora_corta }}</span>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium text-slate-900">{{ $cita->paciente->nombre_completo }}</p>
                        <p class="truncate text-sm text-slate-500">{{ $cita->motivo_consulta ?: 'Sin motivo registrado' }}</p>
                    </div>
                    <x-estado-cita :estado="$cita->estado" />
                </a>
            @empty
                <x-vacio icono="sun" titulo="No tienes sesiones hoy" descripcion="Las nuevas reservas aparecerán aquí automáticamente." />
            @endforelse
        </x-card>

        <x-card titulo="Próximas sesiones" :padding="false">
            <x-tabla-citas :citas="$proximasCitas" :psicologo="false" vacio="No tienes sesiones próximas." />
        </x-card>
    </div>
</x-layouts.app>
