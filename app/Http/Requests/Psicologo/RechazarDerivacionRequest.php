<?php

namespace App\Http\Requests\Psicologo;

use Illuminate\Foundation\Http\FormRequest;

/**
 * RF-05: el rechazo de una derivacion siempre se justifica (CP-UT-49).
 */
class RechazarDerivacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('responder', $this->route('derivacion')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'motivo_rechazo' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}
