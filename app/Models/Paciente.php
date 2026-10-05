<?php

namespace App\Models;

use App\Enums\EstadoAtencion;
use App\Models\Concerns\Auditable;
use Database\Factories\PacienteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

/**
 * Ficha del paciente (RF-05). Puede existir sin cuenta de usuario cuando la
 * recepcionista lo registra en persona o por telefono.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $nombres
 * @property string $apellidos
 * @property string|null $dni
 * @property int|null $edad
 * @property string|null $correo
 * @property string|null $telefono
 * @property string|null $direccion
 * @property string|null $motivo_consulta
 * @property int|null $psicologo_id
 * @property EstadoAtencion $estado_atencion
 * @property-read User|null $psicologoAsignado
 * @property-read string $nombre_completo
 * @property-read User|null $user
 */
class Paciente extends Model
{
    /** @use HasFactory<PacienteFactory> */
    use Auditable, HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'psicologo_id',
        'nombres',
        'apellidos',
        'dni',
        'edad',
        'correo',
        'telefono',
        'direccion',
        'motivo_consulta',
        'estado_atencion',
    ];

    protected $attributes = [
        'estado_atencion' => 'inscripto',
    ];

    protected function casts(): array
    {
        return [
            'edad' => 'integer',
            'estado_atencion' => EstadoAtencion::class,
        ];
    }

    /**
     * Las notificaciones del paciente se envian a su correo de contacto.
     */
    public function routeNotificationForMail(): ?string
    {
        return $this->correo ?: $this->user?->email;
    }

    /**
     * @param  Builder<Paciente>  $query
     */
    public function scopeBuscar(Builder $query, ?string $termino): void
    {
        if (blank($termino)) {
            return;
        }

        $query->where(function (Builder $q) use ($termino) {
            $q->where('nombres', 'like', "%{$termino}%")
                ->orWhere('apellidos', 'like', "%{$termino}%")
                ->orWhere('dni', 'like', "%{$termino}%")
                ->orWhere('correo', 'like', "%{$termino}%");
        });
    }

    /**
     * @return Attribute<string, never>
     */
    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(fn () => trim("{$this->nombres} {$this->apellidos}"));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Psicologo que acepto la derivacion del paciente (RF-05).
     *
     * @return BelongsTo<User, $this>
     */
    public function psicologoAsignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'psicologo_id');
    }

    /**
     * @return HasMany<Derivacion, $this>
     */
    public function derivaciones(): HasMany
    {
        return $this->hasMany(Derivacion::class);
    }

    /**
     * @return HasMany<Cita, $this>
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    /**
     * @return HasMany<HistorialClinico, $this>
     */
    public function historialesClinicos(): HasMany
    {
        return $this->hasMany(HistorialClinico::class);
    }
}
