<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecepcionistaRequest;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Gestion de cuentas de recepcionista (solo administrador).
 */
class RecepcionistaController extends Controller
{
    public function index(): View
    {
        $recepcionistas = User::recepcionistas()
            ->orderBy('name')
            ->paginate(15);

        return view('admin.recepcionistas.index', compact('recepcionistas'));
    }

    public function create(): View
    {
        return view('admin.recepcionistas.create', [
            'recepcionista' => new User(['activo' => true]),
        ]);
    }

    public function store(RecepcionistaRequest $request): RedirectResponse
    {
        User::create([
            ...collect($request->validated())->only(['name', 'apellidos', 'email', 'dni', 'telefono', 'password'])->all(),
            'role' => Rol::Recepcionista,
            'activo' => $request->boolean('activo', true),
        ]);

        return redirect()->route('admin.recepcionistas.index')->with('status', 'Recepcionista registrada correctamente.');
    }

    public function edit(User $recepcionista): View
    {
        $this->asegurarRecepcionista($recepcionista);

        return view('admin.recepcionistas.edit', compact('recepcionista'));
    }

    public function update(RecepcionistaRequest $request, User $recepcionista): RedirectResponse
    {
        $this->asegurarRecepcionista($recepcionista);

        $datos = $request->validated();
        $cambios = collect($datos)->only(['name', 'apellidos', 'email', 'dni', 'telefono'])->all();
        $cambios['activo'] = $request->boolean('activo');

        if (! empty($datos['password'])) {
            $cambios['password'] = $datos['password'];
        }

        $recepcionista->update($cambios);

        return redirect()->route('admin.recepcionistas.index')->with('status', 'Datos actualizados correctamente.');
    }

    public function destroy(User $recepcionista): RedirectResponse
    {
        $this->asegurarRecepcionista($recepcionista);

        $tienePagos = Pago::where('validado_por', $recepcionista->id)
            ->orWhere('registrado_por', $recepcionista->id)
            ->exists();

        if ($tienePagos) {
            $recepcionista->update(['activo' => false]);

            return back()->with('status', 'La recepcionista tiene pagos procesados: se desactivó en lugar de eliminarse.');
        }

        $recepcionista->delete();

        return back()->with('status', 'Recepcionista eliminada.');
    }

    private function asegurarRecepcionista(User $usuario): void
    {
        abort_unless($usuario->esRecepcionista(), 404);
    }
}
