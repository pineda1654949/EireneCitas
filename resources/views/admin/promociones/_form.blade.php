<div class="grid gap-5 sm:grid-cols-2">
    <x-form.input name="nombre" label="Nombre" :value="$promocion->nombre" required class="sm:col-span-2" />
    <x-form.textarea name="descripcion" label="Descripción" :value="$promocion->descripcion" rows="3" class="sm:col-span-2" />
    <x-form.input name="numero_sesiones" label="Número de sesiones" type="number" min="1" max="50" :value="$promocion->numero_sesiones ?? 1" required />
    <x-form.input name="precio" label="Precio (S/)" type="number" min="0" step="0.01" :value="$promocion->precio" required />
</div>

<x-form.checkbox name="activa" label="Promoción activa" :checked="$promocion->activa" class="mt-6"
                 descripcion="Solo las promociones activas se ofrecen al solicitar una cita." />
