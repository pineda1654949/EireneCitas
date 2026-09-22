<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Eirene') - Sistema de Gestion de Citas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #4f6f8f 0%, #7b9cb5 100%);
        }
        .auth-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0,0,0,.175);
        }
        .brand-logo {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: #4f6f8f;
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card auth-card p-4 p-md-5">
                <div class="text-center mb-3">
                    <div class="brand-logo"><i class="bi bi-flower1"></i></div>
                    <h3 class="fw-bold mb-0">Eirene</h3>
                    <p class="text-muted small">Sistema de Gestion de Citas</p>
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
            </div>
        </div>
    </div>
</div>
</body>
</html>
