<?php

namespace App\Models;

use App\Enums\EstadoCita;
use App\Enums\EstadoPago;
use App\Models\Concerns\Auditable;
use Database\Factories\CitaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $paciente_id
 * @property int $psicologo_id
 * @property int|null $especialidad_id
 * @property int|null $promocion_id
 * @property Carbon $fecha
 * @property string $hora
 * @property string|null $motivo_consulta
 * @property EstadoCita $estado
 * @property int $numero_reprogramaciones
 * @property string|null $enlace_meet
 * @property int|null $creado_por
 * @property Carbon|null $created_at
 * @property-read string $hora_corta
 * @property-read Carbon $inicio
 * @property-read Paciente $paciente
 * @property-read User $psicologo
 * @property-read Especialidad|null $especialidad
 * @property-read Promocion|null $promocion
 * @property-read Collection<int, Pago> $pagos
 * @property-read Collection<int, Reprogramacion> $reprogramaciones
 * @property-read HistorialClinico|null $historialClinico
 */
class Cita extends Model
{
    /** @use HasFactory<CitaFactory> */
    use Auditable, HasFactory;

    protected $fillable = [
        'paciente_id',
        'psicologo_id',
        'especialidad_id',
        'promocion_id',
        'fecha',
        'hora',
        'motivo_consulta',
        'estado',
        'numero_reprogramaciones',
        'enlace_meet',
        'creado_por',
    ];

    protected $attributes = [
        'estado' => 'pendiente',
        'numero_reprogramaciones' => 0,
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'estado' => EstadoCita::class,
            'numero_reprogramaciones' => 'integer',
        ];
    }

    public static function maxReprogramaciones(): int
    {
        return (int) config('eirene.citas.max_reprogramaciones');
    }

    // ----- Relaciones -----

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
     * @return BelongsTo<Promocion, $this>
     */
    public function promocion(): BelongsTo
    {
        return $this->belongsTo(Promocion::class);
    }

    /**
     * @return HasMany<Pago, $this>
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    /**
     * @return HasMany<Reprogramacion, $this>
     */
    public function reprogramaciones(): HasMany
    {
        return $this->hasMany(Reprogramacion::class);
    }

    /**
     * @return HasOne<HistorialClinico, $this>
     */
    public function historialClinico(): HasOne
    {
        return $this->hasOne(HistorialClinico::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // ----- Scopes -----

    /**
     * Citas que ocupan el horario del psicologo.
     *
     * @param  Builder<Cita>  $query
     */
    public function scopeActivas(Builder $query): void
    {
        $query->whereIn('estado', EstadoCita::valoresActivos());
    }

    /**
     * Restringe el listado a lo que el usuario tiene permitido ver.
     *
     * @param  Builder<Cita>  $query
     */
    public function scopeVisiblesPara(Builder $query, User $usuario): void
    {
        if ($usuario->esPsicologo()) {
            $query->where('psicologo_id', $usuario->id);
        } elseif ($usuario->esPaciente()) {
            $query->where('paciente_id', $usuario->pacienteFicha->id ?? 0);
        }
    }

    // ----- Atributos -----

    /**
     * La hora se guarda siempre como HH:MM:SS (igual que la columna TIME de MySQL).
     *
     * @return Attribute<never, string>
     */
    protected function hora(): Attribute
    {
        return Attribute::set(fn (string $valor) => Horario::normalizarHora($valor));
    }

    /**
     * @return Attribute<string, never>
     */
    protected function horaCorta(): Attribute
    {
        return Attribute::get(fn () => substr((string) $this->hora, 0, 5));
    }

    /**
     * @return Attribute<Carbon, never>
     */
    protected function inicio(): Attribute
    {
        return Attribute::get(fn () => Carbon::parse($this->fecha->toDateString().' '.$this->hora));
    }

    // ----- Reglas de negocio -----

    public function puedeReprogramarse(): bool
    {
        return $this->numero_reprogramaciones < self::maxReprogramaciones()
            && ! $this->estado->estaCerrada();
    }

    public function reprogramacionesRestantes(): int
    {
        return max(0, self::maxReprogramaciones() - $this->numero_reprogramaciones);
    }

    public function puedeCancelarse(): bool
    {
        return ! $this->estado->estaCerrada();
    }

    public function puedeConfirmarse(): bool
    {
        return $this->estado->admiteConfirmacion() && $this->pagoConfirmado();
    }

    public function pagoConfirmado(): bool
    {
        if ($this->relationLoaded('pagos')) {
            return $this->pagos->contains(fn (Pago $pago) => $pago->estado === EstadoPago::Confirmado);
        }

        return $this->pagos()->where('estado', EstadoPago::Confirmado->value)->exists();
    }
}
