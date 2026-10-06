@include('errors.layout', ['codigo' => 403, 'titulo' => 'Acceso no permitido', 'mensaje' => $exception->getMessage() ?: 'No tienes permisos para ver esta página.'])
