<?php

namespace App\Http\Requests\Admin;

use App\Enums\MetodoPago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gestionarPagos', $this->route('cita')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'monto' => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'metodo_pago' => ['required', Rule::enum(MetodoPago::class)],
            'numero_comprobante' => ['nullable', 'string', 'max:100'],
        ];
    }
}
