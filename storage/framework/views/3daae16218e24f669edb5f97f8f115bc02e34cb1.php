<?php $__env->startSection('titulo', 'Solicitar cita'); ?>

<?php $__env->startSection('content'); ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?php echo e(route('citas.store')); ?>">
                <?php echo csrf_field(); ?>

                <?php if(isset($pacientes)): ?>
                    <div class="mb-3">
                        <label class="form-label">Paciente</label>
                        <select name="paciente_id" class="form-select" required>
                            <option value="">Selecciona el paciente...</option>
                            <?php $__currentLoopData = $pacientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>" <?php echo e(old('paciente_id') == $p->id ? 'selected' : ''); ?>><?php echo e($p->nombre_completo); ?> <?php if($p->dni): ?> (<?php echo e($p->dni); ?>) <?php endif; ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="form-text">¿El paciente es nuevo? <a href="<?php echo e(route('admin.pacientes.create')); ?>" target="_blank">Registralo aqui</a> y luego actualiza esta lista.</div>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Motivo de consulta / especialidad</label>
                        <select id="especialidad_id" name="especialidad_id" class="form-select" required>
                            <option value="">Selecciona una opcion...</option>
                            <?php $__currentLoopData = $especialidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($e->id); ?>" <?php echo e(old('especialidad_id') == $e->id ? 'selected' : ''); ?>><?php echo e($e->nombre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Psicologo</label>
                        <select id="psicologo_id" name="psicologo_id" class="form-select" required>
                            <option value="">Primero elige una especialidad...</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" id="fecha" name="fecha" class="form-control" min="<?php echo e(now()->toDateString()); ?>" value="<?php echo e(old('fecha')); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Hora disponible</label>
                        <select id="hora" name="hora" class="form-select" required>
                            <option value="">Elige psicologo y fecha primero...</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Promocion (opcional)</label>
                    <select name="promocion_id" class="form-select">
                        <option value="">Sesion individual (sin promocion)</option>
                        <?php $__currentLoopData = $promociones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($promo->id); ?>" <?php echo e(old('promocion_id') == $promo->id ? 'selected' : ''); ?>>
                                <?php echo e($promo->nombre); ?> - <?php echo e($promo->numero_sesiones); ?> sesion(es) - S/ <?php echo e(number_format($promo->precio, 2)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Cuentanos brevemente el motivo de tu consulta (opcional)</label>
                    <textarea name="motivo_consulta" class="form-control" rows="3"><?php echo e(old('motivo_consulta')); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Registrar cita
                </button>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const especialidadSelect = document.getElementById('especialidad_id');
const psicologoSelect = document.getElementById('psicologo_id');
const fechaInput = document.getElementById('fecha');
const horaSelect = document.getElementById('hora');
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

especialidadSelect.addEventListener('change', async function () {
    psicologoSelect.innerHTML = '<option value="">Cargando...</option>';
    horaSelect.innerHTML = '<option value="">Elige psicologo y fecha primero...</option>';

    if (!this.value) {
        psicologoSelect.innerHTML = '<option value="">Primero elige una especialidad...</option>';
        return;
    }

    const res = await fetch(`/api/especialidades/${this.value}/psicologos`, {
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    });
    const psicologos = await res.json();

    psicologoSelect.innerHTML = '<option value="">Selecciona un psicologo...</option>';
    psicologos.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = `${p.name} ${p.apellidos ?? ''}`;
        psicologoSelect.appendChild(opt);
    });

    if (psicologos.length === 0) {
        psicologoSelect.innerHTML = '<option value="">No hay psicologos disponibles para esta especialidad</option>';
    }
});

async function cargarHoras() {
    if (!psicologoSelect.value || !fechaInput.value) {
        return;
    }
    horaSelect.innerHTML = '<option value="">Cargando horas disponibles...</option>';

    const params = new URLSearchParams({ psicologo_id: psicologoSelect.value, fecha: fechaInput.value });
    const res = await fetch(`/api/horas-disponibles?${params.toString()}`, {
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    });
    const horas = await res.json();

    if (horas.length === 0) {
        horaSelect.innerHTML = '<option value="">No hay horas disponibles ese dia</option>';
        return;
    }

    horaSelect.innerHTML = '<option value="">Selecciona una hora...</option>';
    horas.forEach(h => {
        const opt = document.createElement('option');
        opt.value = h;
        opt.textContent = h;
        horaSelect.appendChild(opt);
    });
}

psicologoSelect.addEventListener('change', cargarHoras);
fechaInput.addEventListener('change', cargarHoras);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.panel', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\EireneCitas\resources\views/citas/create.blade.php ENDPATH**/ ?>