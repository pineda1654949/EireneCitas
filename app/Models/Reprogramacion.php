<?php

namespace App\Models;

use Database\Factories\ReprogramacionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Historial de reprogramaciones y cancelaciones de una cita (RF-03).
 *
 * @property int $id
 * @property int $cita_id
 * @property int|null $realizado_por
 * @property string $tipo
 * @property Carbon|null $fecha_anterior
 * @property string|null $hora_anterior
 * @property Carbon|null $fecha_nueva
 * @property string|null $hora_nueva
 * @property string|null $motivo
 * @property Carbon $created_at
 * @property-read Cita $cita
 * @property-read User|null $usuario
 */
class Reprogramacion extends Model
{
    /** @use HasFactory<ReprogramacionFactory> */
    use HasFactory;

    public const TIPO_REPROGRAMACION = 'reprogramacion';

    public const TIPO_CANCELACION = 'cancelacion';

    protected $table = 'reprogramaciones';

    protected $fillable = [
        'cita_id',
        'realizado_por',
        'tipo',
        'fecha_anterior',
        'hora_anterior',
        'fecha_nueva',
        'hora_nueva',
        'motivo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_anterior' => 'date',
            'fecha_nueva' => 'date',
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
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'realizado_por');
    }
}
