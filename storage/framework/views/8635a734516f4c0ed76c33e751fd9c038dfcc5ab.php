<?php $__env->startSection('titulo', 'Citas'); ?>

<?php $__env->startSection('content'); ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th><th>Hora</th><th>Paciente</th><th>Psicologo</th>
                        <th>Especialidad</th><th>Estado</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $citas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cita): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($cita->fecha->format('d/m/Y')); ?></td>
                            <td><?php echo e(\Illuminate\Support\Carbon::parse($cita->hora)->format('H:i')); ?></td>
                            <td><?php echo e($cita->paciente->nombre_completo); ?></td>
                            <td><?php echo e($cita->psicologo->name); ?> <?php echo e($cita->psicologo->apellidos); ?></td>
                            <td><?php echo e(optional($cita->especialidad)->nombre); ?></td>
                            <td><span class="badge bg-<?php echo e($cita->estado_badge); ?>"><?php echo e(ucfirst($cita->estado)); ?></span></td>
                            <td class="text-end"><a href="<?php echo e(route('citas.show', $cita)); ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No hay citas registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body">
            <?php echo e($citas->links()); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\EireneCitas\resources\views/citas/index.blade.php ENDPATH**/ ?>