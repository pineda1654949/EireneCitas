<?php

namespace App\Models;

use App\Enums\Rol;
use App\Models\Concerns\Auditable;
use App\Notifications\RestablecerContrasena;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string|null $apellidos
 * @property string $email
 * @property string|null $dni
 * @property string|null $telefono
 * @property Rol $role
 * @property bool $activo
 * @property-read string $nombre_completo
 * @property-read Paciente|null $pacienteFicha
 * @property-read Collection<int, Especialidad> $especialidades
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'apellidos',
        'email',
        'password',
        'dni',
        'telefono',
        'role',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Rol::class,
            'activo' => 'boolean',
        ];
    }

    // ----- Scopes -----

    /**
     * @param  Builder<User>  $query
     */
    public function scopePsicologos(Builder $query): void
    {
        $query->where('role', Rol::Psicologo->value);
    }

    /**
     * @param  Builder<User>  $query
     */
    public function scopeActivos(Builder $query): void
    {
        $query->where('activo', true);
    }

    // ----- Roles -----

    public function tieneRol(Rol ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function esAdministrador(): bool
    {
        return $this->role === Rol::Administrador;
    }

    public function esRecepcionista(): bool
    {
        return $this->role === Rol::Recepcionista;
    }

    public function esPsicologo(): bool
    {
        return $this->role === Rol::Psicologo;
    }

    public function esPaciente(): bool
    {
        return $this->role === Rol::Paciente;
    }

    public function esPersonalAdministrativo(): bool
    {
        return $this->role->esPersonalAdministrativo();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(fn () => trim("{$this->name} {$this->apellidos}"));
    }

    /**
     * @return Attribute<uppercase-string, never>
     */
    protected function iniciales(): Attribute
    {
        return Attribute::get(fn () => mb_strtoupper(
            mb_substr($this->name, 0, 1).mb_substr((string) $this->apellidos, 0, 1)
        ));
    }

    // ----- Relaciones -----

    /**
     * @return BelongsToMany<Especialidad, $this>
     */
    public function especialidades(): BelongsToMany
    {
        return $this->belongsToMany(Especialidad::class, 'especialidad_psicologo', 'user_id', 'especialidad_id')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Horario, $this>
     */
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'psicologo_id');
    }

    /**
     * @return HasMany<Cita, $this>
     */
    public function citasComoPsicologo(): HasMany
    {
        return $this->hasMany(Cita::class, 'psicologo_id');
    }

    /**
     * @return HasMany<HistorialClinico, $this>
     */
    public function historialesClinicos(): HasMany
    {
        return $this->hasMany(HistorialClinico::class, 'psicologo_id');
    }

    /**
     * @return HasOne<Paciente, $this>
     */
    public function pacienteFicha(): HasOne
    {
        return $this->hasOne(Paciente::class, 'user_id');
    }

    /**
     * Envia el enlace de recuperacion con un correo en espanol.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new RestablecerContrasena($token));
    }
}
