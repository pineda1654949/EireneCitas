<?php

/*
|--------------------------------------------------------------------------
| Punto de entrada para hosting compartido sin acceso a la raiz del dominio
|--------------------------------------------------------------------------
| En InfinityFree la raiz web es htdocs/ y no se puede apuntar a public/.
| Por eso el contenido de public/ va directamente en htdocs/ y el resto de la
| aplicacion en htdocs/eirene/, protegida con su propio .htaccess.
| Este archivo reemplaza a public/index.php solo en el paquete de despliegue.
*/

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$aplicacion = __DIR__.'/eirene';

// Modo mantenimiento (php artisan down)
if (file_exists($maintenance = $aplicacion.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $aplicacion.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $aplicacion.'/bootstrap/app.php';

// Los assets compilados (build/) y el favicon estan en htdocs/, no en eirene/public.
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
