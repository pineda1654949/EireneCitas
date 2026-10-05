<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistroRequest;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Registro publico de pacientes (RF-05): crea la cuenta con rol paciente
 * y su ficha clinica en una sola transaccion.
 */
class RegistroController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegistroRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        $usuario = DB::transaction(function () use ($datos) {
            $usuario = User::create([
                'name' => $datos['name'],
                'apellidos' => $datos['apellidos'],
                'dni' => $datos['dni'] ?? null,
                'email' => $datos['email'],
                'telefono' => $datos['telefono'] ?? null,
                'password' => $datos['password'],
                'role' => Rol::Paciente,
            ]);

            Paciente::create([
                'user_id' => $usuario->id,
                'nombres' => $datos['name'],
                'apellidos' => $datos['apellidos'],
                'dni' => $datos['dni'] ?? null,
                'correo' => $datos['email'],
                'telefono' => $datos['telefono'] ?? null,
            ]);

            return $usuario;
        });

        event(new Registered($usuario));

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'Registro exitoso. ¡Bienvenido(a) a Eirene!');
    }
}
