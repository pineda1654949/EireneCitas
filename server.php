<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * Este archivo es requerido por el comando "php artisan serve". Permite que
 * el servidor embebido de PHP emule el comportamiento de mod_rewrite de
 * Apache, redirigiendo todas las peticiones hacia public/index.php.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
