<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Panel') - Eirene</title>
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
    @stack('estilos')
</head>
<body>
<div class="d-flex">
    <nav class="sidebar p-3" style="width: 260px; position: sticky; top: 0; height: 100vh;">
        <div class="brand d-flex align-items-center gap-2 mb-4 px-1">
            <i class="bi bi-flower1 fs-4"></i>
            <span class="fs-5">Eirene</span>
        </div>

        <div class="px-1 mb-3">
            <div class="text-white">{{ auth()->user()->name }} {{ auth()->user()->apellidos }}</div>
            <span class="badge bg-secondary badge-rol">{{ auth()->user()->role }}</span>
        </div>

        <div class="d-flex flex-column gap-1">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> Panel principal
            </a>

            @if(auth()->user()->role === 'paciente')
                <a href="{{ route('citas.create') }}" class="{{ request()->routeIs('citas.create') ? 'active' : '' }}">
                    <i class="bi bi-calendar-plus me-2"></i> Solicitar cita
                </a>
                <a href="{{ route('citas.index') }}" class="{{ request()->routeIs('citas.index') || request()->routeIs('citas.show') ? 'active' : '' }}">
                    <i class="bi bi-journal-text me-2"></i> Mis citas
                </a>
            @endif

            @if(in_array(auth()->user()->role, ['recepcionista','administrador']))
                <a href="{{ route('citas.create') }}" class="{{ request()->routeIs('citas.create') ? 'active' : '' }}">
                    <i class="bi bi-calendar-plus me-2"></i> Registrar cita
                </a>
                <a href="{{ route('citas.index') }}" class="{{ request()->routeIs('citas.index') || request()->routeIs('citas.show') ? 'active' : '' }}">
                    <i class="bi bi-journal-text me-2"></i> Citas
                </a>
                <a href="{{ route('admin.pacientes.index') }}" class="{{ request()->routeIs('admin.pacientes.*') ? 'active' : '' }}">
                    <i class="bi bi-people me-2"></i> Pacientes
                </a>
                <a href="{{ route('pagos.index') }}" class="{{ request()->routeIs('pagos.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card me-2"></i> Pagos
                </a>
                <a href="{{ route('reportes.index') }}" class="{{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart me-2"></i> Reportes
                </a>
            @endif

            @if(auth()->user()->role === 'administrador')
                <a href="{{ route('admin.psicologos.index') }}" class="{{ request()->routeIs('admin.psicologos.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge me-2"></i> Psicologos
                </a>
                <a href="{{ route('admin.promociones.index') }}" class="{{ request()->routeIs('admin.promociones.*') ? 'active' : '' }}">
                    <i class="bi bi-tags me-2"></i> Promociones
                </a>
            @endif

            @if(auth()->user()->role === 'psicologo')
                <a href="{{ route('citas.index') }}" class="{{ request()->routeIs('citas.index') || request()->routeIs('citas.show') ? 'active' : '' }}">
                    <i class="bi bi-journal-text me-2"></i> Mis citas
                </a>
                <a href="{{ route('psicologo.horarios.index') }}" class="{{ request()->routeIs('psicologo.horarios.*') ? 'active' : '' }}">
                    <i class="bi bi-clock me-2"></i> Mi disponibilidad
                </a>
            @endif
        </div>

        <hr class="border-secondary">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="btn btn-outline-light btn-sm w-100" type="submit">
                <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesion
            </button>
        </form>
    </nav>

    <main class="flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">@yield('titulo', 'Panel')</h4>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
