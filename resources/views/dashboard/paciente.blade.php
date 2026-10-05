<x-layouts.app :titulo="'Hola, '.auth()->user()->name" subtitulo="Aquí puedes ver y gestionar tus sesiones.">
    <x-slot:acciones>
        <a href="{{ route('citas.create') }}" class="btn btn-primary"><x-heroicon-o-plus class="size-5" /> Solicitar cita</a>
    </x-slot:acciones>

    @if ($proximaCita)
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 p-6 text-white shadow-sm sm:p-8">
            <div class="absolute -top-16 -right-16 size-56 rounded-full bg-white/10 blur-2xl"></div>
            <p class="text-sm font-medium text-brand-100">Tu próxima sesión</p>
            <p class="mt-2 text-2xl font-semibold sm:text-3xl">
                {{ ucfirst($proximaCita->fecha->isoFormat('dddd D [de] MMMM')) }} · {{ $proximaCita->hora_corta }} h
            </p>
            <p class="mt-1 text-brand-100">
                Con {{ $proximaCita->psicologo->nombre_completo }}
                @if ($proximaCita->especialidad) · {{ $proximaCita->especialidad->nombre }} @endif
            </p>
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-medium">{{ $proximaCita->estado->etiqueta() }}</span>
                <a href="{{ route('citas.show', $proximaCita) }}" class="btn btn-sm bg-white text-brand-700 hover:bg-brand-50">Ver detalle</a>
            </div>
        </section>
    @else
        <x-card>
            <x-vacio icono="calendar-days" titulo="No tienes sesiones próximas"
                     descripcion="Solicita una cita y elige el horario que mejor se adapte a ti.">
                <a href="{{ route('citas.create') }}" class="btn btn-primary">Solicitar mi cita</a>
            </x-vacio>
        </x-card>
    @endif

    <x-card titulo="Mis citas recientes" :padding="false" class="mt-6">
        <x-slot:acciones>
            <a href="{{ route('citas.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Ver historial</a>
        </x-slot:acciones>
        <x-tabla-citas :citas="$misCitas" :paciente="false" :especialidad="true" vacio="Aún no has solicitado citas." />
    </x-card>

    @unless ($paciente)
        <p class="mt-6 rounded-xl bg-amber-50 p-4 text-sm text-amber-800 ring-1 ring-amber-600/20">
            Tu cuenta no tiene una ficha de paciente asociada. Comunícate con la clínica para completarla.
        </p>
    @endunless
</x-layouts.app>
