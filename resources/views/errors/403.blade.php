<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso denegado - Eirene</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="height:100vh; background:#f4f6f9;">
    <div class="text-center">
        <h1 class="display-4 fw-bold text-danger">403</h1>
        <p class="lead">{{ $exception->getMessage() ?: 'No tienes permisos para acceder a esta seccion.' }}</p>
        <a href="{{ route('home') }}" class="btn btn-primary">Volver al panel</a>
    </div>
</body>
</html>
