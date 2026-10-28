<?php

namespace App\Http\Requests\Admin;

use App\Models\Especialidad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EspecialidadRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('nombre')) {
            $this->merge(['nombre' => trim($this->string('nombre')->toString())]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $especialidad = $this->route('especialidad');
        $id = $especialidad instanceof Especialidad ? $especialidad->id : null;

        return [
            'nombre' => ['required', 'string', 'max:100', Rule::unique('especialidades', 'nombre')->ignore($id)],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['descripcion' => 'descripción'];
    }
}