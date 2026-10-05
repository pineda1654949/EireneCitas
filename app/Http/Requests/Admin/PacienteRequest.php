<?php

namespace App\Http\Requests\Admin;

use App\Models\Paciente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * RF-05: datos del paciente (registro y actualizacion).
 */
class PacienteRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $paciente = $this->route('paciente');

        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:150'],
            'dni' => [
                'nullable', 'digits_between:8,12',
                Rule::unique('pacientes', 'dni')->ignore($paciente instanceof Paciente ? $paciente->id : null),
            ],
            'edad' => ['nullable', 'integer', 'min:0', 'max:120'],
            'correo' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'motivo_consulta' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
