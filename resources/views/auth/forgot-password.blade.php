<x-layouts.guest titulo="¿Olvidaste tu contraseña?" subtitulo="Escribe el correo de tu cuenta y te enviaremos un enlace para crear una nueva.">
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <x-form.input name="email" label="Correo electrónico" type="email" autocomplete="email" required autofocus />

        <button type="submit" class="btn btn-primary w-full">
            <x-heroicon-o-envelope class="size-5" />
            Enviar enlace de recuperación
        </button>
    </form>

    <p class="mt-8 text-center text-sm">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1 font-semibold text-brand-600 hover:text-brand-700">
            <x-heroicon-m-arrow-left class="size-4" /> Volver al inicio de sesión
        </a>
    </p>
</x-layouts.guest>
