<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras HTTP de seguridad recomendadas por OWASP.
 * La Content-Security-Policy se omite mientras corre el servidor de
 * desarrollo de Vite (npm run dev), que sirve los assets desde otro origen.
 */
class CabecerasDeSeguridad
{
    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        $cabeceras = $respuesta->headers;
        $cabeceras->set('X-Content-Type-Options', 'nosniff');
        $cabeceras->set('X-Frame-Options', 'DENY');
        $cabeceras->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $cabeceras->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $cabeceras->set('Cross-Origin-Opener-Policy', 'same-origin');

        if (! Vite::isRunningHot()) {
            $cabeceras->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self'",
                // Los estilos en linea solo se usan para anchos de barras en reportes.
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data:",
                "font-src 'self'",
                "connect-src 'self'",
                "form-action 'self'",
                "frame-ancestors 'none'",
                "base-uri 'self'",
                "object-src 'none'",
            ]));
        }

        if ($request->isSecure()) {
            $cabeceras->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $respuesta;
    }
}
