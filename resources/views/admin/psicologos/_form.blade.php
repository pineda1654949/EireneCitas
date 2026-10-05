@php
    $seleccionadas = collect(old('especialidades', $psicologo->exists ? $psicologo->especialidades->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id);
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <x-form.input name="name" label="Nombres" :value="$psicologo->name" required />
    <x-form.input name="apellidos" label="Apellidos" :value="$psicologo->apellidos" required />
    <x-form.input name="email" label="Correo electrónico" type="email" :value="$psicologo->email" required
                  hint="Lo usará para iniciar sesión." />
    <x-form.input name="dni" label="DNI" :value="$psicologo->dni" inputmode="numeric" maxlength="12" />
    <x-form.input name="telefono" label="Teléfono" type="tel" :value="$psicologo->telefono" />
    <x-form.input name="password" :label="$psicologo->exists ? 'Nueva contraseña' : 'Contraseña'" type="password" autocomplete="new-password"
                  :required="! $psicologo->exists"
                  :hint="$psicologo->exists ? 'Déjala vacía para mantener la actual.' : 'Mínimo 8 caracteres, con letras y números.'" />
</div>

<fieldset class="mt-6">
    <legend class="form-label">Especialidades que atiende</legend>
    <div class="mt-2 grid gap-2 sm:grid-cols-2">
        @foreach ($especialidades as $especialidad)
            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm has-checked:border-brand-500 has-checked:bg-brand-50">
                <input type="checkbox" name="especialidades[]" value="{{ $especialidad->id }}" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                       @checked($seleccionadas->contains($especialidad->id))>
                <span class="font-medium text-slate-700">{{ $especialidad->nombre }}</span>
            </label>
        @endforeach
    </div>
    @error('especialidades.*') <p class="form-error">{{ $message }}</p> @enderror
</fieldset>

<x-form.checkbox name="activo" label="Cuenta activa" :checked="$psicologo->activo ?? true" class="mt-6"
                 descripcion="Si se desactiva, no podrá iniciar sesión ni recibir nuevas reservas." />
