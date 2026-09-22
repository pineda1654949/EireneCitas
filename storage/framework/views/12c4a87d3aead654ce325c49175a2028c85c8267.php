<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('titulo', 'Panel'); ?> - Eirene</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar {
            min-height: 100vh;
            background: #2f3d4f;
            color: #cfd8e3;
        }
        .sidebar .brand { color: #fff; font-weight: 700; }
        .sidebar a {
            color: #cfd8e3;
            text-decoration: none;
            display: block;
            padding: .65rem 1rem;
            border-radius: .5rem;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #4f6f8f;
            color: #fff;
        }
        .badge-rol {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        main { padding: 1.75rem; }
    </style>
    <?php echo $__env->yieldPushContent('estilos'); ?>
</head>
<body>
<div class="d-flex">
    <nav class="sidebar p-3" style="width: 260px; position: sticky; top: 0; height: 100vh;">
        <div class="brand d-flex align-items-center gap-2 mb-4 px-1">
            <i class="bi bi-flower1 fs-4"></i>
            <span class="fs-5">Eirene</span>
        </div>

        <div class="px-1 mb-3">
            <div class="text-white"><?php echo e(auth()->user()->name); ?> <?php echo e(auth()->user()->apellidos); ?></div>
            <span class="badge bg-secondary badge-rol"><?php echo e(auth()->user()->role); ?></span>
        </div>

        <div class="d-flex flex-column gap-1">
            <a href="<?php echo e(route('home')); ?>" class="<?php echo e(request()->routeIs('home') ? 'active' : ''); ?>">
                <i class="bi bi-speedometer2 me-2"></i> Panel principal
            </a>

            <?php if(auth()->user()->role === 'paciente'): ?>
                <a href="<?php echo e(route('citas.create')); ?>" class="<?php echo e(request()->routeIs('citas.create') ? 'active' : ''); ?>">
                    <i class="bi bi-calendar-plus me-2"></i> Solicitar cita
                </a>
                <a href="<?php echo e(route('citas.index')); ?>" class="<?php echo e(request()->routeIs('citas.index') || request()->routeIs('citas.show') ? 'active' : ''); ?>">
                    <i class="bi bi-journal-text me-2"></i> Mis citas
                </a>
            <?php endif; ?>

            <?php if(in_array(auth()->user()->role, ['recepcionista','administrador'])): ?>
                <a href="<?php echo e(route('citas.create')); ?>" class="<?php echo e(request()->routeIs('citas.create') ? 'active' : ''); ?>">
                    <i class="bi bi-calendar-plus me-2"></i> Registrar cita
                </a>
                <a href="<?php echo e(route('citas.index')); ?>" class="<?php echo e(request()->routeIs('citas.index') || request()->routeIs('citas.show') ? 'active' : ''); ?>">
                    <i class="bi bi-journal-text me-2"></i> Citas
                </a>
                <a href="<?php echo e(route('admin.pacientes.index')); ?>" class="<?php echo e(request()->routeIs('admin.pacientes.*') ? 'active' : ''); ?>">
                    <i class="bi bi-people me-2"></i> Pacientes
                </a>
                <a href="<?php echo e(route('pagos.index')); ?>" class="<?php echo e(request()->routeIs('pagos.*') ? 'active' : ''); ?>">
                    <i class="bi bi-credit-card me-2"></i> Pagos
                </a>
                <a href="<?php echo e(route('reportes.index')); ?>" class="<?php echo e(request()->routeIs('reportes.*') ? 'active' : ''); ?>">
                    <i class="bi bi-bar-chart me-2"></i> Reportes
                </a>
            <?php endif; ?>

            <?php if(auth()->user()->role === 'administrador'): ?>
                <a href="<?php echo e(route('admin.psicologos.index')); ?>" class="<?php echo e(request()->routeIs('admin.psicologos.*') ? 'active' : ''); ?>">
                    <i class="bi bi-person-badge me-2"></i> Psicologos
                </a>
                <a href="<?php echo e(route('admin.promociones.index')); ?>" class="<?php echo e(request()->routeIs('admin.promociones.*') ? 'active' : ''); ?>">
                    <i class="bi bi-tags me-2"></i> Promociones
                </a>
            <?php endif; ?>

            <?php if(auth()->user()->role === 'psicologo'): ?>
                <a href="<?php echo e(route('citas.index')); ?>" class="<?php echo e(request()->routeIs('citas.index') || request()->routeIs('citas.show') ? 'active' : ''); ?>">
                    <i class="bi bi-journal-text me-2"></i> Mis citas
                </a>
                <a href="<?php echo e(route('psicologo.horarios.index')); ?>" class="<?php echo e(request()->routeIs('psicologo.horarios.*') ? 'active' : ''); ?>">
                    <i class="bi bi-clock me-2"></i> Mi disponibilidad
                </a>
            <?php endif; ?>
        </div>

        <hr class="border-secondary">
        <form action="<?php echo e(route('logout')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button class="btn btn-outline-light btn-sm w-100" type="submit">
                <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesion
            </button>
        </form>
    </nav>

    <main class="flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><?php echo $__env->yieldContent('titulo', 'Panel'); ?></h4>
        </div>

        <?php if(session('status')): ?>
            <div class="alert alert-success"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\EireneCitas\resources\views/layouts/panel.blade.php ENDPATH**/ ?>