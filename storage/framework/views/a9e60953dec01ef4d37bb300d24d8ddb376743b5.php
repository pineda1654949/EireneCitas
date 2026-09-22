<?php $__env->startSection('titulo', 'Promociones'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-end mb-3">
        <a href="<?php echo e(route('admin.promociones.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva promocion
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Nombre</th><th>Sesiones</th><th>Precio</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $promociones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($promo->nombre); ?><div class="text-muted small"><?php echo e($promo->descripcion); ?></div></td>
                            <td><?php echo e($promo->numero_sesiones); ?></td>
                            <td>S/ <?php echo e(number_format($promo->precio, 2)); ?></td>
                            <td><span class="badge bg-<?php echo e($promo->activa ? 'success' : 'secondary'); ?>"><?php echo e($promo->activa ? 'Activa' : 'Inactiva'); ?></span></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('admin.promociones.edit', $promo)); ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                <form action="<?php echo e(route('admin.promociones.destroy', $promo)); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta promocion?');">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay promociones registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body"><?php echo e($promociones->links()); ?></div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\EireneCitas\resources\views/admin/promociones/index.blade.php ENDPATH**/ ?>