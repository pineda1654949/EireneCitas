<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware de control de acceso segun el rol del usuario (RF-07).
 * Uso en rutas: ->middleware('role:administrador,recepcionista')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!in_array($request->user()->role, $roles)) {
            abort(403, 'No tienes permisos para acceder a este modulo.');
        }

        return $next($request);
    }
}
