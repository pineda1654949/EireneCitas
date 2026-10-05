<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PsicologoRequest;
use App\Models\Especialidad;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Gestion de psicologos y sus especialidades (Tabla 1 del documento).
 */
class PsicologoController extends Controller
{
    public function index(): View
    {
        $psicologos = User::psicologos()
            ->with('especialidades')
            ->withCount('citasComoPsicologo')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.psicologos.index', compact('psicologos'));
    }

    public function create(): View
    {
        return view('admin.psicologos.create', [
            'psicologo' => new User(['activo' => true]),
            'especialidades' => Especialidad::orderBy('nombre')->get(),
        ]);
    }

    public function store(PsicologoRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        DB::transaction(function () use ($datos, $request) {
            $psicologo = User::create([
                ...collect($datos)->only(['name', 'apellidos', 'email', 'dni', 'telefono', 'password'])->all(),
                'role' => Rol::Psicologo,
                'activo' => $request->boolean('activo', true),
            ]);

            $psicologo->especialidades()->sync($datos['especialidades'] ?? []);
        });

        return redirect()->route('admin.psicologos.index')->with('status', 'Psicologo registrado correctamente.');
    }

    public function edit(User $psicologo): View
    {
        $this->asegurarPsicologo($psicologo);

        $psicologo->load('especialidades');

        return view('admin.psicologos.edit', [
            'psicologo' => $psicologo,
            'especialidades' => Especialidad::orderBy('nombre')->get(),
        ]);
    }

    public function update(PsicologoRequest $request, User $psicologo): RedirectResponse
    {
        $this->asegurarPsicologo($psicologo);

        $datos = $request->validated();

        DB::transaction(function () use ($datos, $request, $psicologo) {
            $cambios = collect($datos)->only(['name', 'apellidos', 'email', 'dni', 'telefono'])->all();
            $cambios['activo'] = $request->boolean('activo');

            if (! empty($datos['password'])) {
                $cambios['password'] = $datos['password'];
            }

            $psicologo->update($cambios);
            $psicologo->especialidades()->sync($datos['especialidades'] ?? []);
        });

        return redirect()->route('admin.psicologos.index')->with('status', 'Datos actualizados correctamente.');
    }

    public function destroy(User $psicologo): RedirectResponse
    {
        $this->asegurarPsicologo($psicologo);

        // Con citas registradas se desactiva en lugar de borrar, para no
        // perder la trazabilidad de sus sesiones e historias clinicas.
        if ($psicologo->citasComoPsicologo()->exists()) {
            $psicologo->update(['activo' => false]);

            return back()->with('status', 'El psicologo tiene citas registradas: se desactivo en lugar de eliminarse.');
        }

        $psicologo->delete();

        return back()->with('status', 'Psicologo eliminado.');
    }

    /**
     * Evita editar o eliminar desde este modulo a un usuario que no es
     * psicologo (p. ej. cambiando el id en la URL).
     */
    private function asegurarPsicologo(User $usuario): void
    {
        abort_unless($usuario->esPsicologo(), 404);
    }
}
