<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Sin enlaces propios en el contenedor: los servicios de la aplicacion
        // se resuelven por autowiring y toda la configuracion vive en boot().
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        // En desarrollo y pruebas se detectan consultas N+1 y atributos
        // ignorados; en produccion nunca se interrumpe al usuario por ello.
        Model::shouldBeStrict(! $this->app->isProduction());

        if ($proxies = config('eirene.proxies_confiables')) {
            TrustProxies::at($proxies);
        }

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Politica de contrasenas para cuentas nuevas y cambios de contrasena.
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        RateLimiter::for('formularios-publicos', function (Request $request) {
            return Limit::perMinute((int) config('eirene.limite_formularios_por_minuto'))->by($request->ip());
        });
    }
}
