<?php

namespace App\Models;

use App\Enums\EstadoDerivacion;
use App\Models\Concerns\Auditable;
use Database\Factories\DerivacionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Derivacion de un paciente a un psicologo (RF-05).
 *
 * @property int $id
 * @property int $paciente_id
 * @property int $psicologo_id
 * @property int|null $especialidad_id
 * @property int|null $derivado_por
 * @property string|null $observaciones
 * @property EstadoDerivacion $estado
 * @property string|null $motivo_rechazo
 * @property Carbon|null $respondida_at
 * @property Carbon|null $created_at
 * @property-read Paciente $paciente
 * @property-read User $psicologo
 * @property-read Especialidad|null $especialidad
 * @property-read User|null $derivadoPor
 */
class Derivacion extends Model
{
    /** @use HasFactory<DerivacionFactory> */
    use Auditable, HasFactory;

    protected $table = 'derivaciones';

    protected $fillable = [
        'paciente_id',
        'psicologo_id',
        'especialidad_id',
        'derivado_por',
        'observaciones',
        'estado',
        'motivo_rechazo',
        'respondida_at',
    ];

    protected $attributes = [
        'estado' => 'pendiente',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoDerivacion::class,
            'respondida_at' => 'datetime',
        ];
    }

    public function estaPendiente(): bool
    {
        return $this->estado === EstadoDerivacion::Pendiente;
    }

    /**
     * @return BelongsTo<Paciente, $this>
     */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function psicologo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'psicologo_id');
    }

    /**
     * @return BelongsTo<Especialidad, $this>
     */
    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function derivadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'derivado_por');
    }
}
