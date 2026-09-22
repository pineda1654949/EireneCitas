<?php $__env->startSection('titulo', 'Panel del administrador'); ?>

<?php $__env->startSection('content'); ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Pacientes registrados</div>
                <div class="fs-3 fw-bold"><?php echo e($totalPacientes); ?></div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Psicologos activos</div>
                <div class="fs-3 fw-bold"><?php echo e($totalPsicologos); ?></div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Citas pendientes</div>
                <div class="fs-3 fw-bold text-warning"><?php echo e($citasPendientes); ?></div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Citas confirmadas</div>
                <div class="fs-3 fw-bold text-success"><?php echo e($citasConfirmadas); ?></div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Sesiones atendidas</div>
                <div class="fs-4 fw-bold text-primary"><?php echo e($citasAtendidas); ?></div>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Citas canceladas</div>
                <div class="fs-4 fw-bold text-danger"><?php echo e($citasCanceladas); ?></div>
            </div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Ultimas citas registradas</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Paciente</th><th>Psicologo</th><th>Fecha</th><th>Hora</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $ultimasCitas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cita): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><a href="<?php echo e(route('citas.show', $cita)); ?>"><?php echo e($cita->paciente->nombre_completo); ?></a></td>
                            <td><?php echo e($cita->psicologo->name); ?> <?php echo e($cita->psicologo->apellidos); ?></td>
                            <td><?php echo e($cita->fecha->format('d/m/Y')); ?></td>
                            <td><?php echo e(\Illuminate\Support\Carbon::parse($cita->hora)->format('H:i')); ?></td>
                            <td><span class="badge bg-<?php echo e($cita->estado_badge); ?>"><?php echo e(ucfirst($cita->estado)); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Aun no hay citas registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\EireneCitas\resources\views/dashboard/admin.blade.php ENDPATH**/ ?>