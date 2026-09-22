@php $p = $paciente ?? null; @endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Nombres</label>
        <input type="text" name="nombres" class="form-control" value="{{ old('nombres', $p->nombres ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Apellidos</label>
        <input type="text" name="apellidos" class="form-control" value="{{ old('apellidos', $p->apellidos ?? '') }}" required>
    </div>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">DNI</label>
        <input type="text" name="dni" class="form-control" value="{{ old('dni', $p->dni ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Edad</label>
        <input type="number" name="edad" class="form-control" min="0" max="120" value="{{ old('edad', $p->edad ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Telefono</label>
        <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $p->telefono ?? '') }}">
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Correo</label>
    <input type="email" name="correo" class="form-control" value="{{ old('correo', $p->correo ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Direccion</label>
    <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $p->direccion ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Motivo de consulta / observaciones</label>
    <textarea name="motivo_consulta" class="form-control" rows="3">{{ old('motivo_consulta', $p->motivo_consulta ?? '') }}</textarea>
</div>
