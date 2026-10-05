<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PsicologoRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $psicologo = $this->route('psicologo');
        $id = $psicologo instanceof User ? $psicologo->id : null;

        return [
            'name' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'dni' => ['nullable', 'digits_between:8,12', Rule::unique('users', 'dni')->ignore($id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            // Obligatoria al crear; al editar solo si se quiere cambiar.
            'password' => [$id ? 'nullable' : 'required', Password::defaults()],
            'activo' => ['sometimes', 'boolean'],
            'especialidades' => ['array'],
            'especialidades.*' => ['integer', 'exists:especialidades,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'nombres', 'especialidades.*' => 'especialidad'];
    }
}
