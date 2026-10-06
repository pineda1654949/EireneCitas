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
 * @property int $numero_cuota
 * @property int $total_cuotas
 * @property string|null $comprobante_path
 * @property string|null $comprobante_nombre
 * @property int|null $registrado_por
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
        'numero_cuota',
        'total_cuotas',
        'estado',
        'numero_comprobante',
        'comprobante_path',
        'comprobante_nombre',
        'fecha_pago',
        'validado_por',
        'registrado_por',
    ];

    protected $attributes = [
        'estado' => 'pendiente',
        'numero_cuota' => 1,
        'total_cuotas' => 1,
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'metodo_pago' => MetodoPago::class,
            'estado' => EstadoPago::class,
            'fecha_pago' => 'datetime',
            'numero_cuota' => 'integer',
            'total_cuotas' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Cita, $this>
     */
    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function tieneComprobante(): bool
    {
        return $this->comprobante_path !== null;
    }

    public function etiquetaCuota(): string
    {
        return $this->total_cuotas > 1 ? "Cuota {$this->numero_cuota} de {$this->total_cuotas}" : 'Pago al contado';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function validadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por');
    }
}
