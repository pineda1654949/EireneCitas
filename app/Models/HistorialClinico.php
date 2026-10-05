<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\HistorialClinicoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Registro clinico de una sesion atendida (RF-06).
 *
 * @property int $id
 * @property int $cita_id
 * @property int $paciente_id
 * @property int $psicologo_id
 * @property string $notas_sesion
 * @property string|null $avance
 * @property string|null $documento_path
 * @property Carbon|null $created_at
 * @property-read Cita $cita
 * @property-read Paciente $paciente
 * @property-read User $psicologo
 */
class HistorialClinico extends Model
{
    /** @use HasFactory<HistorialClinicoFactory> */
    use Auditable, HasFactory;

    protected $table = 'historiales_clinicos';

    /**
     * Las notas clinicas son datos sensibles de salud: la auditoria registra
     * que cambiaron, pero no copia su contenido.
     *
     * @var list<string>
     */
    protected array $noAuditar = ['notas_sesion'];

    protected $fillable = [
        'cita_id',
        'paciente_id',
        'psicologo_id',
        'notas_sesion',
        'avance',
        'documento_path',
    ];

    /**
     * Las notas se guardan cifradas en la base de datos (APP_KEY).
     */
    protected function casts(): array
    {
        return [
            'notas_sesion' => 'encrypted',
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
}
