<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function mostrarLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credenciales, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ])->onlyInput('email');
        }

        if (!Auth::user()->activo) {
            Auth::logout();
            return back()->withErrors(['email' => 'Tu cuenta se encuentra inactiva. Contacta al administrador.']);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('home'));
    }

    /**
     * Registro publico. Segun el documento (TO-BE), el paciente completa la
     * solicitud de cita en el sistema web con sus datos personales (RF-05).
     * Se crea el usuario con rol "paciente" y su ficha en la tabla pacientes.
     */
    public function mostrarRegistro()
    {
        return view('auth.register');
    }

    public function registro(Request $request)
    {
        $datos = $request->validate([
            'name' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => 'nullable|string|max:15|unique:users,dni',
            'email' => 'required|string|email|max:255|unique:users,email',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $datos['name'],
            'apellidos' => $datos['apellidos'],
            'dni' => $datos['dni'] ?? null,
            'email' => $datos['email'],
            'telefono' => $datos['telefono'] ?? null,
            'password' => Hash::make($datos['password']),
            'role' => 'paciente',
        ]);

        Paciente::create([
            'user_id' => $user->id,
            'nombres' => $datos['name'],
            'apellidos' => $datos['apellidos'],
            'dni' => $datos['dni'] ?? null,
            'correo' => $datos['email'],
            'telefono' => $datos['telefono'] ?? null,
        ]);

        Auth::login($user);

        return redirect()->route('home')->with('status', 'Registro exitoso. ¡Bienvenido(a) a Eirene!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
