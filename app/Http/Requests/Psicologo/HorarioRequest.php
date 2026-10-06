<?php

namespace App\Http\Requests\Psicologo;

use App\Models\Horario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class HorarioRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dia_semana' => ['required', 'integer', 'between:0,6'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ];
    }

    /**
     * Un bloque nuevo no puede cruzarse con otro bloque del mismo dia.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $seCruza = Horario::where('psicologo_id', $this->user()?->id)
                    ->where('dia_semana', $this->integer('dia_semana'))
                    ->where('hora_inicio', '<', Horario::normalizarHora($this->string('hora_fin')->toString()))
                    ->where('hora_fin', '>', Horario::normalizarHora($this->string('hora_inicio')->toString()))
                    ->exists();

                if ($seCruza) {
                    $validator->errors()->add('hora_inicio', 'El bloque se cruza con otro horario que ya registraste ese día.');
                }
            },
        ];
    }
}
