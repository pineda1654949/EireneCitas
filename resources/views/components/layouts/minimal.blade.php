@props(['titulo'])

{{-- Layout de las paginas de error: no depende de la sesion ni de la base de datos. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $titulo }} · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css'])
</head>
<body class="flex h-full items-center justify-center bg-slate-50 px-6">
    <main class="w-full max-w-md text-center">
        <x-logo class="mx-auto size-12" />
        {{ $slot }}
    </main>
</body>
</html>
