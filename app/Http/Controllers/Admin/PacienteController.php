<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PacienteRequest;
use App\Models\Paciente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * RF-05: registro y actualizacion de datos del paciente.
 * Accesible por administrador y recepcionista (Tabla 8 del documento).
 */
class PacienteController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = $request->string('buscar')->trim()->limit(100, '')->toString();

        $pacientes = Paciente::query()
            ->withCount('citas')
            ->buscar($buscar)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pacientes.index', compact('pacientes', 'buscar'));
    }

    public function create(): View
    {
        return view('admin.pacientes.create', ['paciente' => new Paciente]);
    }

    public function store(PacienteRequest $request): RedirectResponse
    {
        Paciente::create($request->validated());

        return redirect()->route('admin.pacientes.index')->with('status', 'Paciente registrado correctamente.');
    }

    public function edit(Paciente $paciente): View
    {
        return view('admin.pacientes.edit', compact('paciente'));
    }

    public function update(PacienteRequest $request, Paciente $paciente): RedirectResponse
    {
        $paciente->update($request->validated());

        return redirect()->route('admin.pacientes.index')->with('status', 'Datos del paciente actualizados.');
    }

    public function destroy(Paciente $paciente): RedirectResponse
    {
        // Un paciente con citas tiene historial clinico asociado: no se borra.
        if ($paciente->citas()->exists()) {
            return back()->withErrors([
                'paciente' => 'No se puede eliminar un paciente con citas registradas: su historial debe conservarse.',
            ]);
        }

        $paciente->delete();

        return back()->with('status', 'Paciente eliminado.');
    }
}
