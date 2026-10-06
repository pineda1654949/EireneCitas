<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * Solicitud del enlace de recuperacion de contrasena por correo.
 */
class RecuperarContrasenaController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        // Siempre la misma respuesta: no revela si el correo esta registrado.
        return back()->with('status', 'Si el correo está registrado, te enviamos un enlace para restablecer tu contraseña.');
    }
}
