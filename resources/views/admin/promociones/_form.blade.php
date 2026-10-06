<div class="grid gap-5 sm:grid-cols-2">
    <x-form.input name="nombre" label="Nombre" :value="$promocion->nombre" required class="sm:col-span-2" />
    <x-form.textarea name="descripcion" label="Descripción" :value="$promocion->descripcion" rows="3" class="sm:col-span-2" />
    <x-form.input name="numero_sesiones" label="Número de sesiones" type="number" min="1" max="50" :value="$promocion->numero_sesiones ?? 1" required />
    <x-form.input name="precio" label="Precio total (S/)" type="number" min="0.01" step="0.01" :value="$promocion->precio" required />
</div>

<div class="mt-6 space-y-4 rounded-xl border border-slate-200 p-4">
    <x-form.checkbox name="permite_cuotas" label="Permite pago en cuotas" :checked="$promocion->permite_cuotas"
                     descripcion="El paciente podrá pagar el paquete en varias cuotas (RF-03)." />
    <x-form.input name="max_cuotas" label="Máximo de cuotas" type="number" min="2" max="12"
                  :value="$promocion->permite_cuotas ? $promocion->max_cuotas : 2" class="sm:max-w-xs"
                  hint="Entre 2 y 12. Solo aplica si se permite el pago en cuotas." />
</div>

<x-form.checkbox name="activa" label="Promoción activa" :checked="$promocion->activa" class="mt-6"
                 descripcion="Solo las promociones activas se ofrecen al solicitar una cita." />
