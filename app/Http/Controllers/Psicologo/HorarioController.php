<?php

namespace App\Http\Controllers\Psicologo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Psicologo\HorarioRequest;
use App\Models\Horario;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * El psicologo configura sus bloques de atencion semanales. Se consultan en
 * tiempo real (RF-02), reemplazando el Excel mensual del proceso AS-IS.
 */
class HorarioController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $psicologo */
        $psicologo = $request->user();

        $horarios = $psicologo->horarios()
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get()
            ->sortBy(fn (Horario $h) => $h->dia_semana === 0 ? 7 : $h->dia_semana) // lunes primero
            ->groupBy('dia_semana');

        return view('psicologo.horarios.index', compact('horarios'));
    }

    public function store(HorarioRequest $request): RedirectResponse
    {
        /** @var User $psicologo */
        $psicologo = $request->user();

        $psicologo->horarios()->create($request->validated());

        return back()->with('status', 'Horario agregado correctamente.');
    }

    public function alternar(Request $request, Horario $horario): RedirectResponse
    {
        abort_unless($horario->psicologo_id === $request->user()?->id, 403);

        $horario->update(['activo' => ! $horario->activo]);

        return back()->with('status', $horario->activo ? 'Bloque activado.' : 'Bloque pausado: no se ofrecerán esas horas.');
    }

    public function destroy(Request $request, Horario $horario): RedirectResponse
    {
        abort_unless($horario->psicologo_id === $request->user()?->id, 403);

        $horario->delete();

        return back()->with('status', 'Horario eliminado.');
    }
}
