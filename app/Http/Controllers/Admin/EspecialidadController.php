<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EspecialidadRequest;
use App\Models\Especialidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Gestion de las especialidades que atiende la clinica (solo administrador).
 */
class EspecialidadController extends Controller
{
    public function index(): View
    {
        $especialidades = Especialidad::withCount(['psicologos', 'citas'])
            ->orderBy('nombre')
            ->paginate(15);

        return view('admin.especialidades.index', compact('especialidades'));
    }

    public function create(): View
    {
        return view('admin.especialidades.create', [
            'especialidad' => new Especialidad,
        ]);
    }

    public function store(EspecialidadRequest $request): RedirectResponse
    {
        Especialidad::create($request->validated());

        return redirect()->route('admin.especialidades.index')->with('status', 'Especialidad registrada correctamente.');
    }

    public function edit(Especialidad $especialidad): View
    {
        return view('admin.especialidades.edit', compact('especialidad'));
    }

    public function update(EspecialidadRequest $request, Especialidad $especialidad): RedirectResponse
    {
        $especialidad->update($request->validated());

        return redirect()->route('admin.especialidades.index')->with('status', 'Especialidad actualizada correctamente.');
    }

    public function destroy(Especialidad $especialidad): RedirectResponse
    {
        // Si se borrara, sus citas quedarian sin especialidad y los
        // psicologos perderian esa asignacion.
        if ($especialidad->psicologos()->exists() || $especialidad->citas()->exists()) {
            return back()->withErrors([
                'especialidad' => "No se puede eliminar «{$especialidad->nombre}»: tiene psicólogos o citas asociadas.",
            ]);
        }

        $especialidad->delete();

        return back()->with('status', 'Especialidad eliminada.');
    }
}