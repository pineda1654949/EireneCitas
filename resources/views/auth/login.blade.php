<x-layouts.guest titulo="Inicia sesión" subtitulo="Ingresa con tu usuario o correo para gestionar tus citas.">
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-form.input name="email" label="Usuario o correo electrónico" autocomplete="username" required autofocus />

        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <label for="password" class="text-sm font-medium text-slate-700">Contraseña <span class="text-rose-500" aria-hidden="true">*</span></label>
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">¿La olvidaste?</a>
            </div>
            <x-form.input name="password" type="password" autocomplete="current-password" required />
        </div>

        <x-form.checkbox name="remember" label="Mantener la sesión iniciada" />

        <button type="submit" class="btn btn-primary w-full">
            Ingresar
            <x-heroicon-m-arrow-right class="size-4" />
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        ¿Eres un paciente nuevo?
        <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:text-brand-700">Crea tu cuenta</a>
    </p>

    @unless (app()->isProduction())
        <div class="mt-8 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-xs text-slate-600">
            <p class="font-semibold text-slate-700">Cuentas de demostración</p>
            <p class="mt-1">Contraseña de todas: <code class="rounded bg-white px-1 py-0.5 ring-1 ring-slate-200">contraseña</code></p>
            <p class="mt-1 leading-relaxed">admin · recepcion@eirene.test · psicologo1@eirene.test · paciente@eirene.test</p>
        </div>
    @endunless
</x-layouts.guest>
