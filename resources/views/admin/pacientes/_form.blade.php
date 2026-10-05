{{-- Campos compartidos por el registro y la edicion de pacientes (RF-05). --}}
<div class="grid gap-5 sm:grid-cols-2">
    <x-form.input name="nombres" label="Nombres" :value="$paciente->nombres" required />
    <x-form.input name="apellidos" label="Apellidos" :value="$paciente->apellidos" required />
    <x-form.input name="dni" label="DNI" :value="$paciente->dni" inputmode="numeric" maxlength="12" />
    <x-form.input name="edad" label="Edad" type="number" min="0" max="120" :value="$paciente->edad" />
    <x-form.input name="correo" label="Correo electrónico" type="email" :value="$paciente->correo"
                  hint="Se usa para enviar confirmaciones y recordatorios." />
    <x-form.input name="telefono" label="Teléfono" type="tel" :value="$paciente->telefono" />
    <x-form.input name="direccion" label="Dirección" :value="$paciente->direccion" class="sm:col-span-2" />
    <x-form.textarea name="motivo_consulta" label="Motivo de consulta / síntomas reportados" :value="$paciente->motivo_consulta" rows="4" class="sm:col-span-2" />
</div>
