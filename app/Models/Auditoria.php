<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $evento
 * @property string|null $auditable_type
 * @property int|null $auditable_id
 * @property array<string, mixed>|null $valores_anteriores
 * @property array<string, mixed>|null $valores_nuevos
 * @property string|null $ip
 * @property string|null $user_agent
 * @property Carbon|null $created_at
 * @property-read User|null $usuario
 */
class Auditoria extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    protected $table = 'auditorias';

    protected $fillable = [
        'user_id',
        'evento',
        'auditable_type',
        'auditable_id',
        'valores_anteriores',
        'valores_nuevos',
        'ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'valores_anteriores' => 'array',
            'valores_nuevos' => 'array',
        ];
    }

    /**
     * @param  array<string, mixed>|null  $anteriores
     * @param  array<string, mixed>|null  $nuevos
     */
    public static function registrar(string $evento, ?Model $modelo = null, ?array $anteriores = null, ?array $nuevos = null, ?int $usuarioId = null): self
    {
        $request = app()->runningInConsole() && ! app()->runningUnitTests() ? null : request();

        return static::create([
            'user_id' => $usuarioId ?? auth()->id(),
            'evento' => $evento,
            'auditable_type' => $modelo?->getMorphClass(),
            'auditable_id' => $modelo?->getKey(),
            'valores_anteriores' => $anteriores,
            'valores_nuevos' => $nuevos,
            'ip' => $request?->ip(),
            'user_agent' => $request ? Str::limit((string) $request->userAgent(), 250, '') : null,
        ]);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Nombre legible del tipo de registro (p. ej. "Cita").
     */
    public function tipoLegible(): string
    {
        return $this->auditable_type ? class_basename($this->auditable_type) : 'Sistema';
    }

    /**
     * Los registros se conservan el tiempo configurado (por defecto 1 ano).
     *
     * @return Builder<Auditoria>
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays((int) config('eirene.auditoria.dias_retencion')));
    }
}
