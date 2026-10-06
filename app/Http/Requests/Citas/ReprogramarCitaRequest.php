<?php

namespace App\Http\Requests\Citas;

use Illuminate\Foundation\Http\FormRequest;

class ReprogramarCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reprogramar', $this->route('cita')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            // RN-01: cada cambio queda justificado en el historial de la cita.
            'motivo' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
