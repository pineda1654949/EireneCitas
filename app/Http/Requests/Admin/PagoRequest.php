<?php

namespace App\Http\Requests\Admin;

use App\Enums\MetodoPago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reportarPago', $this->route('cita')) ?? false;
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
            'total_cuotas' => ['nullable', 'integer', 'between:1,12'],
            // Voucher: obligatorio cuando lo reporta el paciente; el personal
            // puede registrar pagos en efectivo sin adjunto.
            'comprobante' => [
                Rule::requiredIf(fn () => ! $this->user()?->esPersonalAdministrativo()),
                'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120',
            ],
        ];
    }
}
