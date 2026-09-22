<?php $__env->startSection('titulo', 'Reportes e indicadores'); ?>

<?php $__env->startSection('content'); ?>
    <form method="GET" class="row g-2 mb-4">
        <div class="col-auto">
            <label class="form-label small">Desde</label>
            <input type="date" name="desde" class="form-control" value="<?php echo e($desde); ?>">
        </div>
        <div class="col-auto">
            <label class="form-label small">Hasta</label>
            <input type="date" name="hasta" class="form-control" value="<?php echo e($hasta); ?>">
        </div>
        <div class="col-auto d-flex align-items-end">
            <button class="btn btn-primary">Filtrar</button>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Ingresos confirmados</div>
                <div class="fs-4 fw-bold text-success">S/ <?php echo e(number_format($ingresosConfirmados, 2)); ?></div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Total reprogramaciones</div>
                <div class="fs-4 fw-bold text-warning"><?php echo e($totalReprogramaciones); ?></div>
            </div></div>
        </div>
        <?php $__currentLoopData = ['pendiente' => 'warning', 'confirmada' => 'success', 'atendida' => 'primary', 'cancelada' => 'danger']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estado => $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted small">Citas <?php echo e($estado); ?>s</div>
                    <div class="fs-4 fw-bold text-<?php echo e($color); ?>"><?php echo e($citasPorEstado[$estado] ?? 0); ?></div>
                </div></div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Citas atendidas por psicologo</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Psicologo</th><th>Total de citas</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $citasPorPsicologo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fila): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr><td><?php echo e($fila->name); ?> <?php echo e($fila->apellidos); ?></td><td><?php echo e($fila->total); ?></td></tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="2" class="text-center text-muted py-4">Sin datos en el rango seleccionado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\EireneCitas\resources\views/admin/reportes/index.blade.php ENDPATH**/ ?>