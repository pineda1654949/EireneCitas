<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RecepcionistaRequest extends FormRequest
{
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
        $recepcionista = $this->route('recepcionista');
        $id = $recepcionista instanceof User ? $recepcionista->id : null;

        return [
            'name' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'dni' => ['nullable', 'digits_between:8,12', Rule::unique('users', 'dni')->ignore($id)],
            'telefono' => ['nullable', 'regex:/^9\d{8}$/'],
            'password' => [$id ? 'nullable' : 'required', Password::defaults()],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'nombres'];
    }
}