<x-layouts.guest titulo="Crea tu cuenta de paciente" subtitulo="Regístrate para solicitar y dar seguimiento a tus citas.">
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input name="name" label="Nombres" autocomplete="given-name" required autofocus />
            <x-form.input name="apellidos" label="Apellidos" autocomplete="family-name" required />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input name="dni" label="DNI" inputmode="numeric" maxlength="12" />
            <x-form.input name="telefono" label="Teléfono" type="tel" autocomplete="tel" />
        </div>

        <x-form.input name="email" label="Correo electrónico" type="email" autocomplete="email" required />

        <x-form.input name="password" label="Contraseña" type="password" autocomplete="new-password" required
                      hint="Mínimo 8 caracteres, con letras y números." />
        <x-form.input name="password_confirmation" label="Confirma la contraseña" type="password" autocomplete="new-password" required />

        <button type="submit" class="btn btn-primary w-full">Crear cuenta</button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        ¿Ya tienes una cuenta?
        <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Inicia sesión</a>
    </p>
</x-layouts.guest>
