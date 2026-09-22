<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Gestion de psicologos: el administrador asigna al psicologo adecuado
 * segun los sintomas del paciente y su disponibilidad (Tabla 1 del documento).
 */
class PsicologoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador']);
    }

    public function index()
    {
        $psicologos = User::psicologos()->with('especialidades')->orderBy('name')->paginate(15);
        return view('admin.psicologos.index', compact('psicologos'));
    }

    public function create()
    {
        $especialidades = Especialidad::orderBy('nombre')->get();
        return view('admin.psicologos.create', compact('especialidades'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'name' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'dni' => 'nullable|string|max:15|unique:users,dni',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'especialidades' => 'array',
            'especialidades.*' => 'exists:especialidades,id',
        ]);

        $psicologo = User::create([
            'name' => $datos['name'],
            'apellidos' => $datos['apellidos'],
            'email' => $datos['email'],
            'dni' => $datos['dni'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'password' => Hash::make($datos['password']),
            'role' => 'psicologo',
        ]);

        $psicologo->especialidades()->sync($datos['especialidades'] ?? []);

        return redirect()->route('admin.psicologos.index')->with('status', 'Psicologo registrado correctamente.');
    }

    public function edit(User $psicologo)
    {
        $especialidades = Especialidad::orderBy('nombre')->get();
        $psicologo->load('especialidades');
        return view('admin.psicologos.edit', compact('psicologo', 'especialidades'));
    }

    public function update(Request $request, User $psicologo)
    {
        $datos = $request->validate([
            'name' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $psicologo->id,
            'telefono' => 'nullable|string|max:20',
            'activo' => 'boolean',
            'especialidades' => 'array',
            'especialidades.*' => 'exists:especialidades,id',
        ]);

        $psicologo->update([
            'name' => $datos['name'],
            'apellidos' => $datos['apellidos'],
            'email' => $datos['email'],
            'telefono' => $datos['telefono'] ?? null,
            'activo' => $request->boolean('activo'),
        ]);

        $psicologo->especialidades()->sync($datos['especialidades'] ?? []);

        return redirect()->route('admin.psicologos.index')->with('status', 'Datos actualizados correctamente.');
    }

    public function destroy(User $psicologo)
    {
        $psicologo->delete();
        return back()->with('status', 'Psicologo eliminado.');
    }
}
