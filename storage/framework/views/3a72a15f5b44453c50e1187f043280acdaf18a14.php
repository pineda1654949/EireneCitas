<?php $__env->startSection('titulo', 'Psicologos'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-end mb-3">
        <a href="<?php echo e(route('admin.psicologos.create')); ?>" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Nuevo psicologo
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Nombre</th><th>Correo</th><th>Especialidades</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $psicologos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $psicologo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($psicologo->name); ?> <?php echo e($psicologo->apellidos); ?></td>
                            <td><?php echo e($psicologo->email); ?></td>
                            <td>
                                <?php $__currentLoopData = $psicologo->especialidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="badge bg-light text-dark border"><?php echo e($e->nombre); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo e($psicologo->activo ? 'success' : 'secondary'); ?>">
                                    <?php echo e($psicologo->activo ? 'Activo' : 'Inactivo'); ?>

                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo e(route('admin.psicologos.edit', $psicologo)); ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                <form action="<?php echo e(route('admin.psicologos.destroy', $psicologo)); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este psicologo?');">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay psicologos registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body"><?php echo e($psicologos->links()); ?></div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\EireneCitas\resources\views/admin/psicologos/index.blade.php ENDPATH**/ ?>