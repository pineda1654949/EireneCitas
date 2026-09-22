<?php $__env->startSection('titulo', 'Pacientes'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre o DNI..." value="<?php echo e(request('buscar')); ?>">
            <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
        </form>
        <a href="<?php echo e(route('admin.pacientes.create')); ?>" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Nuevo paciente
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Nombre completo</th><th>DNI</th><th>Telefono</th><th>Correo</th><th></th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $pacientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $paciente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($paciente->nombre_completo); ?></td>
                            <td><?php echo e($paciente->dni ?: '-'); ?></td>
                            <td><?php echo e($paciente->telefono ?: '-'); ?></td>
                            <td><?php echo e($paciente->correo ?: '-'); ?></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('admin.pacientes.edit', $paciente)); ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                <form action="<?php echo e(route('admin.pacientes.destroy', $paciente)); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este paciente?');">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay pacientes registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body"><?php echo e($pacientes->links()); ?></div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\EireneCitas\resources\views/admin/pacientes/index.blade.php ENDPATH**/ ?>