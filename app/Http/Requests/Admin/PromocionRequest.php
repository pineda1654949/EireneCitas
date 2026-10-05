<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PromocionRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'numero_sesiones' => ['required', 'integer', 'min:1', 'max:50'],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999'],
            'activa' => ['sometimes', 'boolean'],
        ];
    }
}
