<?php

namespace App\Models;

use App\Enums\EstadoPago;
use App\Enums\MetodoPago;
use App\Models\Concerns\Auditable;
use Database\Factories\PagoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $cita_id
 * @property string $monto
 * @property MetodoPago $metodo_pago
 * @property EstadoPago $estado
 * @property string|null $numero_comprobante
 * @property Carbon|null $fecha_pago
 * @property int|null $validado_por
 * @property Carbon|null $created_at
 * @property-read Cita $cita
 * @property-read User|null $validadoPor
 */
class Pago extends Model
{
    /** @use HasFactory<PagoFactory> */
    use Auditable, HasFactory;

    protected $fillable = [
        'cita_id',
        'monto',
        'metodo_pago',
        'estado',
        'numero_comprobante',
        'fecha_pago',
        'validado_por',
    ];

    protected $attributes = [
        'estado' => 'pendiente',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'metodo_pago' => MetodoPago::class,
            'estado' => EstadoPago::class,
            'fecha_pago' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Cita, $this>
     */
    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function validadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por');
    }
}
