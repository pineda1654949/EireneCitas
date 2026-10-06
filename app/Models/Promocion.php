<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\PromocionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Paquete de sesiones que ofrece la clinica.
 *
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property int $numero_sesiones
 * @property string $precio
 * @property bool $activa
 * @property bool $permite_cuotas
 * @property int $max_cuotas
 */
class Promocion extends Model
{
    /** @use HasFactory<PromocionFactory> */
    use Auditable, HasFactory;

    protected $table = 'promociones';

    protected $fillable = ['nombre', 'descripcion', 'numero_sesiones', 'precio', 'activa', 'permite_cuotas', 'max_cuotas'];

    protected $attributes = [
        'permite_cuotas' => false,
        'max_cuotas' => 1,
    ];

    protected function casts(): array
    {
        return [
            'numero_sesiones' => 'integer',
            'precio' => 'decimal:2',
            'activa' => 'boolean',
            'permite_cuotas' => 'boolean',
            'max_cuotas' => 'integer',
        ];
    }

    /**
     * Numero maximo de cuotas en que se puede pagar (1 = al contado).
     */
    public function cuotasPermitidas(): int
    {
        return $this->permite_cuotas ? max(1, $this->max_cuotas) : 1;
    }

    /**
     * @param  Builder<Promocion>  $query
     */
    public function scopeActivas(Builder $query): void
    {
        $query->where('activa', true);
    }

    /**
     * @return HasMany<Cita, $this>
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
}
