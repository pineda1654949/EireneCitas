<?php $__env->startSection('titulo', 'Editar psicologo'); ?>

<?php $__env->startSection('content'); ?>
    <div class="card border-0 shadow-sm" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="<?php echo e(route('admin.psicologos.update', $psicologo)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombres</label>
                        <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $psicologo->name)); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellidos</label>
                        <input type="text" name="apellidos" class="form-control" value="<?php echo e(old('apellidos', $psicologo->apellidos)); ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="email" class="form-control" value="<?php echo e(old('email', $psicologo->email)); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Telefono</label>
                    <input type="text" name="telefono" class="form-control" value="<?php echo e(old('telefono', $psicologo->telefono)); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Especialidades que atiende</label>
                    <div class="row">
                        <?php $__currentLoopData = $especialidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="especialidades[]" value="<?php echo e($e->id); ?>" id="esp<?php echo e($e->id); ?>"
                                        @checked($psicologo->especialidades->contains($e->id))>
                                    <label class="form-check-label" for="esp<?php echo e($e->id); ?>"><?php echo e($e->nombre); ?></label>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" @checked($psicologo->activo)>
                    <label class="form-check-label" for="activo">Cuenta activa</label>
                </div>
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="<?php echo e(route('admin.psicologos.index')); ?>" class="btn btn-link">Cancelar</a>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\EireneCitas\resources\views/admin/psicologos/edit.blade.php ENDPATH**/ ?>