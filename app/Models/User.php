<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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

    protected $casts = [
        'email_verified_at' => 'datetime',
        'activo' => 'boolean',
    ];

    // ----- Scopes por rol -----
    public function scopePsicologos($query)
    {
        return $query->where('role', 'psicologo');
    }

    public function scopeRecepcionistas($query)
    {
        return $query->where('role', 'recepcionista');
    }

    public function scopeAdministradores($query)
    {
        return $query->where('role', 'administrador');
    }

    // ----- Helpers de rol -----
    public function esAdministrador()
    {
        return $this->role === 'administrador';
    }

    public function esRecepcionista()
    {
        return $this->role === 'recepcionista';
    }

    public function esPsicologo()
    {
        return $this->role === 'psicologo';
    }

    public function esPaciente()
    {
        return $this->role === 'paciente';
    }

    // ----- Relaciones -----
    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'especialidad_psicologo', 'user_id', 'especialidad_id');
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'psicologo_id');
    }

    public function citasComoPsicologo()
    {
        return $this->hasMany(Cita::class, 'psicologo_id');
    }

    public function historialesClinicos()
    {
        return $this->hasMany(HistorialClinico::class, 'psicologo_id');
    }

    public function pacienteFicha()
    {
        return $this->hasOne(Paciente::class, 'user_id');
    }
}
