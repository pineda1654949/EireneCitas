<div class="grid gap-5 sm:grid-cols-2">
    <x-form.input name="name" label="Nombres" :value="$recepcionista->name" required />
    <x-form.input name="apellidos" label="Apellidos" :value="$recepcionista->apellidos" required />
    <x-form.input name="email" label="Correo electrónico" type="email" :value="$recepcionista->email" required hint="Lo usará para iniciar sesión." />
    <x-form.input name="dni" label="DNI" :value="$recepcionista->dni" inputmode="numeric" maxlength="12" />
    <x-form.input name="telefono" label="Teléfono" type="tel" :value="$recepcionista->telefono" />
    <x-form.input name="password" :label="$recepcionista->exists ? 'Nueva contraseña' : 'Contraseña'" type="password" autocomplete="new-password" :required="! $recepcionista->exists" :hint="$recepcionista->exists ? 'Déjala vacía para mantener la actual.' : 'Mínimo 8 caracteres, con letras y números.'" />
</div>

<x-form.checkbox name="activo" label="Cuenta activa" :checked="$recepcionista->activo ?? true" class="mt-6" descripcion="Si se desactiva, no podrá iniciar sesión." />