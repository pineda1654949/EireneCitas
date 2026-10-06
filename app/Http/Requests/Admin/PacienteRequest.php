<?php

namespace App\Http\Requests\Admin;

use App\Enums\EstadoAtencion;
use App\Models\Paciente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * RF-05: datos del paciente (registro y actualizacion).
 */
class PacienteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('correo')) {
            $this->merge(['correo' => mb_strtolower(trim($this->string('correo')->toString()))]);
        }
    }

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
            'edad' => ['nullable', 'integer', 'min:1', 'max:120'],
            'correo' => [
                'nullable', 'email', 'max:255',
                Rule::unique('pacientes', 'correo')->ignore($paciente instanceof Paciente ? $paciente->id : null),
            ],
            'telefono' => ['nullable', 'regex:/^9\d{8}$/'],
            'estado_atencion' => ['sometimes', Rule::enum(EstadoAtencion::class)],
            'direccion' => ['nullable', 'string', 'max:255'],
            'motivo_consulta' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
