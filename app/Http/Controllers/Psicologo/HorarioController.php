<?php

namespace App\Http\Controllers\Psicologo;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * El psicologo configura sus horarios de atencion. En el proceso TO-BE esto
 * se consulta en tiempo real (RF-02), reemplazando el Excel mensual del AS-IS.
 */
class HorarioController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:psicologo']);
    }

    public function index()
    {
        $horarios = Horario::where('psicologo_id', Auth::id())
            ->orderBy('dia_semana')->orderBy('hora_inicio')->get();

        return view('psicologo.horarios.index', compact('horarios'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'dia_semana' => 'required|integer|min:0|max:6',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
        ]);

        Horario::create([
            'psicologo_id' => Auth::id(),
            'dia_semana' => $datos['dia_semana'],
            'hora_inicio' => $datos['hora_inicio'],
            'hora_fin' => $datos['hora_fin'],
        ]);

        return back()->with('status', 'Horario agregado correctamente.');
    }

    public function destroy(Horario $horario)
    {
        if ($horario->psicologo_id !== Auth::id()) {
            abort(403);
        }
        $horario->delete();
        return back()->with('status', 'Horario eliminado.');
    }
}
