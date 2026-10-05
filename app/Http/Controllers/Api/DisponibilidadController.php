<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use App\Models\Horario;
use Illuminate\Http\Request;

class DisponibilidadController extends Controller
{
    /**
     * Devuelve los psicologos que atienden una especialidad dada.
     * RF-02 / derivacion segun sintomas (Cap. 3.4 del documento).
     */
    public function psicologosPorEspecialidad(Especialidad $especialidad)
    {
        return response()->json(
            $especialidad->psicologos()->select('users.id', 'users.name', 'users.apellidos')->get()
        );
    }

    /**
     * Devuelve las horas disponibles de un psicologo en una fecha dada,
     * cruzando su horario configurado con las citas ya registradas (RF-02).
     */
    public function horasDisponibles(Request $request)
    {
        $request->validate([
            'psicologo_id' => 'required|exists:users,id',
            'fecha' => 'required|date',
            'cita_id' => 'nullable|integer', // al reprogramar, su propia hora cuenta como libre
        ]);

        return response()->json(
            Horario::horasDisponibles($request->psicologo_id, $request->fecha, $request->cita_id)
        );
    }
}
