<x-layouts.guest titulo="Crea una nueva contraseña" subtitulo="Elige una contraseña segura que no uses en otros sitios.">
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-form.input name="email" label="Correo electrónico" type="email" :value="$request->email" autocomplete="email" required />

        <x-form.input name="password" label="Nueva contraseña" type="password" autocomplete="new-password" required autofocus
                      hint="Mínimo 8 caracteres, con letras y números." />
        <x-form.input name="password_confirmation" label="Confirma la contraseña" type="password" autocomplete="new-password" required />

        <button type="submit" class="btn btn-primary w-full">Guardar contraseña</button>
    </form>
</x-layouts.guest>
