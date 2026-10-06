<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * RF-05: derivar un paciente a un psicologo.
 */
class DerivarPacienteRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'psicologo_id' => ['required', 'integer', 'exists:users,id'],
            'especialidad_id' => ['nullable', 'integer', 'exists:especialidades,id'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
