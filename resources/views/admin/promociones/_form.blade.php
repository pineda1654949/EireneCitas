@php $pr = $promocion ?? null; @endphp

<div class="mb-3">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $pr->nombre ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Descripcion</label>
    <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion', $pr->descripcion ?? '') }}</textarea>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Numero de sesiones</label>
        <input type="number" name="numero_sesiones" class="form-control" min="1" value="{{ old('numero_sesiones', $pr->numero_sesiones ?? 1) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Precio (S/)</label>
        <input type="number" step="0.01" name="precio" class="form-control" min="0" value="{{ old('precio', $pr->precio ?? '') }}" required>
    </div>
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" name="activa" value="1" id="activa" @checked(old('activa', $pr->activa ?? true))>
    <label class="form-check-label" for="activa">Promocion activa</label>
</div>
