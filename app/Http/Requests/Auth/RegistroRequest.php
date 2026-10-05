<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegistroRequest extends FormRequest
{
    /**
     * El correo se guarda sin espacios y en minusculas (DEF-009).
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge(['email' => mb_strtolower(trim($this->string('email')->toString()))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:150'],
            // Si recepcion ya registro una ficha con ese DNI no se crea otra:
            // vincularla requiere verificar la identidad en la clinica (DEF-011).
            'dni' => ['nullable', 'digits_between:8,12', 'unique:users,dni', Rule::unique('pacientes', 'dni')],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'nombres'];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'dni.unique' => 'Ya existe un paciente registrado con este DNI. Comunícate con la clínica para activar tu cuenta.',
        ];
    }
}
