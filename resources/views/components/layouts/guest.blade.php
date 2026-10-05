@props(['titulo', 'subtitulo' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titulo }} · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-white">
    <div class="flex min-h-full">
        {{-- Panel de marca (solo en pantallas grandes) --}}
        <div class="relative hidden w-0 flex-1 overflow-hidden bg-gradient-to-br from-brand-700 via-brand-800 to-slate-900 lg:block">
            <div class="absolute -top-24 -left-24 size-96 rounded-full bg-brand-400/20 blur-3xl"></div>
            <div class="absolute -right-32 bottom-0 size-[28rem] rounded-full bg-emerald-300/10 blur-3xl"></div>

            <div class="relative flex h-full flex-col justify-between p-12 text-white">
                <div class="flex items-center gap-3">
                    <x-logo class="size-11" />
                    <span class="text-xl font-semibold">Eirene</span>
                </div>

                <div class="max-w-md">
                    <h2 class="text-3xl leading-tight font-semibold">Tu bienestar emocional, en buenas manos.</h2>
                    <p class="mt-4 text-brand-100">
                        Solicita, reprograma y da seguimiento a tus sesiones con los psicólogos de la clínica, en cualquier momento.
                    </p>

                    <ul class="mt-8 space-y-3 text-sm text-brand-50">
                        <li class="flex items-center gap-3"><x-heroicon-o-calendar-days class="size-5 text-brand-300" /> Horarios disponibles en tiempo real</li>
                        <li class="flex items-center gap-3"><x-heroicon-o-envelope class="size-5 text-brand-300" /> Confirmaciones y recordatorios por correo</li>
                        <li class="flex items-center gap-3"><x-heroicon-o-lock-closed class="size-5 text-brand-300" /> Información clínica protegida y confidencial</li>
                    </ul>
                </div>

                <p class="text-xs text-brand-200/80">&copy; {{ date('Y') }} {{ config('eirene.clinica.nombre') }}</p>
            </div>
        </div>

        {{-- Formulario --}}
        <div class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                <div class="flex items-center gap-3 lg:hidden">
                    <x-logo class="size-10" />
                    <span class="text-lg font-semibold text-slate-900">Eirene</span>
                </div>

                <h1 class="mt-8 text-2xl font-semibold tracking-tight text-slate-900 lg:mt-0">{{ $titulo }}</h1>
                @if ($subtitulo)
                    <p class="mt-2 text-sm text-slate-500">{{ $subtitulo }}</p>
                @endif

                <div class="mt-8">
                    <x-mensajes />
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
