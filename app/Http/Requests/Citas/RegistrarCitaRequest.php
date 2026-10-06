<?php

namespace App\Http\Requests\Citas;

use App\Models\Cita;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrarCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Cita::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'paciente_id' => [
                Rule::requiredIf(fn () => (bool) $this->user()?->esPersonalAdministrativo()),
                'nullable',
                'integer',
                'exists:pacientes,id',
            ],
            'especialidad_id' => ['required', 'integer', 'exists:especialidades,id'],
            'psicologo_id' => ['required', 'integer', 'exists:users,id'],
            'promocion_id' => ['nullable', 'integer', Rule::exists('promociones', 'id')->where('activa', true)],
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'motivo_consulta' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
