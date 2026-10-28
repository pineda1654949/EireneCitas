<div class="grid gap-5">
    <x-form.input name="nombre" label="Nombre" :value="$especialidad->nombre" maxlength="100" required />
    <x-form.textarea name="descripcion" label="Descripción" :value="$especialidad->descripcion" rows="3" />
</div>