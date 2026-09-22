<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;

/**
 * RF-05: Registro y actualizacion de datos del paciente.
 * Accesible por administrador y recepcionista (ver Tabla 8 del documento).
 */
class PacienteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador,recepcionista']);
    }

    public function index(Request $request)
    {
        $query = Paciente::query();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%$buscar%")
                  ->orWhere('apellidos', 'like', "%$buscar%")
                  ->orWhere('dni', 'like', "%$buscar%");
            });
        }

        $pacientes = $query->orderBy('apellidos')->paginate(15)->withQueryString();

        return view('admin.pacientes.index', compact('pacientes'));
    }

    public function create()
    {
        return view('admin.pacientes.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => 'nullable|string|max:15',
            'edad' => 'nullable|integer|min:0|max:120',
            'correo' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'motivo_consulta' => 'nullable|string',
        ]);

        Paciente::create($datos);

        return redirect()->route('admin.pacientes.index')->with('status', 'Paciente registrado correctamente.');
    }

    public function edit(Paciente $paciente)
    {
        return view('admin.pacientes.edit', compact('paciente'));
    }

    public function update(Request $request, Paciente $paciente)
    {
        $datos = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => 'nullable|string|max:15',
            'edad' => 'nullable|integer|min:0|max:120',
            'correo' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'motivo_consulta' => 'nullable|string',
        ]);

        $paciente->update($datos);

        return redirect()->route('admin.pacientes.index')->with('status', 'Datos del paciente actualizados.');
    }

    public function destroy(Paciente $paciente)
    {
        $paciente->delete();
        return back()->with('status', 'Paciente eliminado.');
    }
}
