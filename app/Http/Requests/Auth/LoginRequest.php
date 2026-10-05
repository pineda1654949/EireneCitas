<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /** Intentos fallidos permitidos antes de bloquear temporalmente. */
    public const MAX_INTENTOS = 5;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Acepta correo o nombre de usuario (p. ej. la cuenta "admin").
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['email' => 'usuario o correo'];
    }

    /**
     * Autentica con limite de intentos por usuario e IP (proteccion contra
     * ataques de fuerza bruta) y rechaza cuentas desactivadas.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->asegurarQueNoEstaBloqueado();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->claveDeBloqueo());

            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        RateLimiter::clear($this->claveDeBloqueo());

        if (! Auth::user()?->activo) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta se encuentra inactiva. Contacta al administrador.',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function asegurarQueNoEstaBloqueado(): void
    {
        if (! RateLimiter::tooManyAttempts($this->claveDeBloqueo(), self::MAX_INTENTOS)) {
            return;
        }

        event(new Lockout($this));

        $segundos = RateLimiter::availableIn($this->claveDeBloqueo());

        throw ValidationException::withMessages([
            'email' => "Demasiados intentos de inicio de sesion. Intenta de nuevo en {$segundos} segundos.",
        ]);
    }

    private function claveDeBloqueo(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
