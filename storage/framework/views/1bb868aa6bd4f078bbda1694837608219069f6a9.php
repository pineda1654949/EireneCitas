<?php $__env->startSection('titulo', 'Control de pagos'); ?>

<?php $__env->startSection('content'); ?>
    <form method="GET" class="mb-3 d-flex gap-2">
        <select name="estado" class="form-select" style="max-width: 220px;" onchange="this.form.submit()">
            <option value="">Todos los estados</option>
            <option value="pendiente" @selected(request('estado')==='pendiente')>Pendiente</option>
            <option value="confirmado" @selected(request('estado')==='confirmado')>Confirmado</option>
            <option value="rechazado" @selected(request('estado')==='rechazado')>Rechazado</option>
        </select>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Cita</th><th>Paciente</th><th>Monto</th><th>Metodo</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $pagos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pago): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><a href="<?php echo e(route('citas.show', $pago->cita)); ?>">#<?php echo e($pago->cita_id); ?></a></td>
                            <td><?php echo e($pago->cita->paciente->nombre_completo); ?></td>
                            <td>S/ <?php echo e(number_format($pago->monto, 2)); ?></td>
                            <td><?php echo e(ucfirst(str_replace('_', ' ', $pago->metodo_pago))); ?></td>
                            <td>
                                <span class="badge bg-<?php echo e($pago->estado === 'confirmado' ? 'success' : ($pago->estado === 'rechazado' ? 'danger' : 'warning')); ?>">
                                    <?php echo e(ucfirst($pago->estado)); ?>

                                </span>
                            </td>
                            <td class="text-end">
                                <?php if($pago->estado === 'pendiente'): ?>
                                    <form action="<?php echo e(route('pagos.validar', $pago)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                        <button class="btn btn-sm btn-outline-success">Validar</button>
                                    </form>
                                    <form action="<?php echo e(route('pagos.rechazar', $pago)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                        <button class="btn btn-sm btn-outline-danger">Rechazar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No hay pagos registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body"><?php echo e($pagos->links()); ?></div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\EireneCitas\resources\views/admin/pagos/index.blade.php ENDPATH**/ ?>