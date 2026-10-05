@props(['titulo', 'subtitulo' => null])

@php
    /** @var \App\Models\User $usuario */
    $usuario = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $titulo }} · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:shadow">
        Saltar al contenido
    </a>

    {{-- Fondo oscuro del menu lateral en moviles --}}
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

    {{-- Menu lateral --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-slate-900 transition-transform duration-200 lg:translate-x-0" aria-label="Menú principal">
        <div class="flex h-16 shrink-0 items-center justify-between px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <x-logo class="size-9" />
                <div class="leading-tight">
                    <span class="block text-base font-semibold text-white">Eirene</span>
                    <span class="block text-xs text-slate-400">Gestión de citas</span>
                </div>
            </a>
            <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white lg:hidden" data-sidebar-close aria-label="Cerrar menú">
                <x-heroicon-o-x-mark class="size-6" />
            </button>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-4 py-4">
            <div class="space-y-1">
                <x-nav-link :href="route('home')" :active="request()->routeIs('home')" icono="home">Panel principal</x-nav-link>
            </div>

            @if ($usuario->esPaciente())
                <x-nav-seccion titulo="Mis citas">
                    <x-nav-link :href="route('citas.create')" :active="request()->routeIs('citas.create')" icono="calendar-days">Solicitar cita</x-nav-link>
                    <x-nav-link :href="route('citas.index')" :active="request()->routeIs('citas.index', 'citas.show', 'citas.reprogramar.form', 'citas.cancelar.form')" icono="clipboard-document-list">Historial de citas</x-nav-link>
                </x-nav-seccion>
            @endif

            @if ($usuario->esPersonalAdministrativo())
                <x-nav-seccion titulo="Atención">
                    <x-nav-link :href="route('citas.create')" :active="request()->routeIs('citas.create')" icono="calendar-days">Registrar cita</x-nav-link>
                    <x-nav-link :href="route('citas.index')" :active="request()->routeIs('citas.index', 'citas.show', 'citas.reprogramar.form', 'citas.cancelar.form')" icono="clipboard-document-list">Citas</x-nav-link>
                    <x-nav-link :href="route('admin.pacientes.index')" :active="request()->routeIs('admin.pacientes.*')" icono="users">Pacientes</x-nav-link>
                    <x-nav-link :href="route('admin.derivaciones.index')" :active="request()->routeIs('admin.derivaciones.*')" icono="arrow-right-circle">Derivaciones</x-nav-link>
                    <x-nav-link :href="route('pagos.index')" :active="request()->routeIs('pagos.*')" icono="banknotes">Pagos</x-nav-link>
                    <x-nav-link :href="route('reportes.index')" :active="request()->routeIs('reportes.*')" icono="chart-bar">Reportes</x-nav-link>
                </x-nav-seccion>
            @endif

            @if ($usuario->esAdministrador())
                <x-nav-seccion titulo="Administración">
                    <x-nav-link :href="route('admin.psicologos.index')" :active="request()->routeIs('admin.psicologos.*')" icono="identification">Psicólogos</x-nav-link>
                    <x-nav-link :href="route('admin.promociones.index')" :active="request()->routeIs('admin.promociones.*')" icono="tag">Promociones</x-nav-link>
                    <x-nav-link :href="route('admin.auditoria.index')" :active="request()->routeIs('admin.auditoria.*')" icono="shield-check">Auditoría</x-nav-link>
                </x-nav-seccion>
            @endif

            @if ($usuario->esPsicologo())
                <x-nav-seccion titulo="Consulta">
                    <x-nav-link :href="route('citas.index')" :active="request()->routeIs('citas.*', 'psicologo.historial.*')" icono="clipboard-document-list">Mis citas</x-nav-link>
                    <x-nav-link :href="route('psicologo.derivaciones.index')" :active="request()->routeIs('psicologo.derivaciones.*')" icono="arrow-right-circle">Derivaciones</x-nav-link>
                    <x-nav-link :href="route('psicologo.horarios.index')" :active="request()->routeIs('psicologo.horarios.*')" icono="clock">Mi disponibilidad</x-nav-link>
                </x-nav-seccion>
            @endif
        </nav>

        <div class="border-t border-slate-800 p-4">
            <div class="flex items-center gap-3 rounded-xl px-2 py-2">
                <x-avatar :usuario="$usuario" class="size-9 bg-brand-500/20 text-brand-200" />
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ $usuario->nombre_completo }}</p>
                    <p class="truncate text-xs text-slate-400">{{ $usuario->role->etiqueta() }}</p>
                </div>
            </div>
        </div>
    </aside>

    <div class="lg:pl-72">
        {{-- Barra superior --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white/80 px-4 backdrop-blur sm:px-6 lg:px-10">
            <button type="button" class="-ml-1 rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" data-sidebar-open aria-label="Abrir menú">
                <x-heroicon-o-bars-3 class="size-6" />
            </button>

            <p class="hidden text-sm text-slate-500 sm:block">
                {{ ucfirst(now()->isoFormat('dddd D [de] MMMM [de] YYYY')) }}
            </p>

            <div class="relative ml-auto" data-dropdown>
                <button type="button" class="flex items-center gap-2 rounded-full p-1 pr-2 hover:bg-slate-100" data-dropdown-toggle aria-haspopup="true" aria-expanded="false">
                    <x-avatar :usuario="$usuario" class="size-8 bg-brand-100 text-brand-700" />
                    <span class="hidden text-sm font-medium text-slate-700 sm:block">{{ $usuario->name }}</span>
                    <x-heroicon-m-chevron-down class="size-4 text-slate-400" />
                </button>
                <div class="absolute right-0 z-30 mt-2 hidden w-56 origin-top-right rounded-xl bg-white p-1.5 shadow-lg ring-1 ring-slate-200" data-dropdown-menu>
                    <div class="border-b border-slate-100 px-3 py-2">
                        <p class="truncate text-sm font-medium text-slate-900">{{ $usuario->nombre_completo }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $usuario->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="pt-1">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100">
                            <x-heroicon-o-arrow-right-start-on-rectangle class="size-5 text-slate-400" />
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main id="contenido" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-10">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $titulo }}</h1>
                    @if ($subtitulo)
                        <p class="mt-1 text-sm text-slate-500">{{ $subtitulo }}</p>
                    @endif
                </div>
                @isset($acciones)
                    <div class="flex flex-wrap items-center gap-2">{{ $acciones }}</div>
                @endisset
            </div>

            <x-mensajes />

            {{ $slot }}
        </main>
    </div>
</body>
</html>
