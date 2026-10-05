<?php

namespace App\Http\Requests\Citas;

use Illuminate\Foundation\Http\FormRequest;

class CancelarCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cancelar', $this->route('cita')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'motivo' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
