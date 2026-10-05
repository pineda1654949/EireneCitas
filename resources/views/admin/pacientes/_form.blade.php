@use('App\Enums\EstadoAtencion')

{{-- Campos compartidos por el registro y la edicion de pacientes (RF-02). --}}
<div class="grid gap-5 sm:grid-cols-2">
    <x-form.input name="nombres" label="Nombres" :value="$paciente->nombres" required />
    <x-form.input name="apellidos" label="Apellidos" :value="$paciente->apellidos" required />
    <x-form.input name="dni" label="DNI" :value="$paciente->dni" inputmode="numeric" maxlength="12" pattern="[0-9]{8,12}" />
    <x-form.input name="edad" label="Edad" type="number" min="1" max="120" :value="$paciente->edad" />
    <x-form.input name="correo" label="Correo electrónico" type="email" :value="$paciente->correo"
                  hint="Se usa para enviar confirmaciones y recordatorios." />
    <x-form.input name="telefono" label="Celular" type="tel" :value="$paciente->telefono" inputmode="numeric"
                  maxlength="9" pattern="9[0-9]{8}" placeholder="987654321" hint="9 dígitos, empieza con 9." />
    <x-form.input name="direccion" label="Dirección" :value="$paciente->direccion" class="sm:col-span-2" />
    <x-form.textarea name="motivo_consulta" label="Motivo de consulta / síntomas reportados" :value="$paciente->motivo_consulta" rows="4" class="sm:col-span-2" />

    @if ($paciente->exists)
        <x-form.select name="estado_atencion" label="Estado de atención" class="sm:max-w-xs"
                       hint="Ciclo de vida del paciente en la clínica.">
            @foreach (EstadoAtencion::cases() as $estado)
                <option value="{{ $estado->value }}" @selected(old('estado_atencion', $paciente->estado_atencion->value) === $estado->value)>{{ $estado->etiqueta() }}</option>
            @endforeach
        </x-form.select>
    @endif
</div>
