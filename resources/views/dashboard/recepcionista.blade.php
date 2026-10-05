<x-layouts.app titulo="Recepción" subtitulo="Citas del día y tareas pendientes.">
    <x-slot:acciones>
        <a href="{{ route('admin.pacientes.create') }}" class="btn btn-secondary"><x-heroicon-o-user-plus class="size-5" /> Nuevo paciente</a>
        <a href="{{ route('citas.create') }}" class="btn btn-primary"><x-heroicon-o-plus class="size-5" /> Registrar cita</a>
    </x-slot:acciones>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat etiqueta="Citas para hoy" :valor="$citasHoy->count()" icono="calendar-days" />
        <x-stat etiqueta="Citas pendientes" :valor="$citasPendientes" icono="clock" tono="amber"
                detalle="Sin pago validado" :href="route('citas.index', ['estado' => 'pendiente'])" />
        <x-stat etiqueta="Pagos por validar" :valor="$pagosPorValidar" icono="banknotes" tono="emerald"
                :href="route('pagos.index', ['estado' => 'pendiente'])" />
    </div>

    <x-card titulo="Agenda de hoy" :descripcion="ucfirst(today()->isoFormat('dddd D [de] MMMM'))" :padding="false" class="mt-6">
        <x-tabla-citas :citas="$citasHoy" vacio="No hay citas programadas para hoy." />
    </x-card>
</x-layouts.app>
