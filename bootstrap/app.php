<?php

use App\Exceptions\ReglaDeNegocioException;
use App\Http\Middleware\AsegurarUsuarioActivo;
use App\Http\Middleware\CabecerasDeSeguridad;
use App\Http\Middleware\VerificarRol;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            CabecerasDeSeguridad::class,
            AsegurarUsuarioActivo::class,
        ]);

        $middleware->alias([
            'rol' => VerificarRol::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Una regla de negocio incumplida (p. ej. horario ocupado) vuelve al
        // formulario con el mensaje, en lugar de mostrar una pagina de error.
        $exceptions->render(function (ReglaDeNegocioException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput()->withErrors([$e->campo => $e->getMessage()]);
        });

        $exceptions->dontReport(ReglaDeNegocioException::class);
    })->create();
