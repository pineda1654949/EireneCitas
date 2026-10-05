<?php

namespace App\Http\Requests\Psicologo;

use Illuminate\Foundation\Http\FormRequest;

class HistorialClinicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('atender', $this->route('cita')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notas_sesion' => ['required', 'string', 'max:10000'],
            'avance' => ['nullable', 'string', 'max:255'],
        ];
    }
}
