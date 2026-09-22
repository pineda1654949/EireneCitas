<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombres',
        'apellidos',
        'dni',
        'edad',
        'correo',
        'telefono',
        'direccion',
        'motivo_consulta',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    public function historialesClinicos()
    {
        return $this->hasMany(HistorialClinico::class);
    }

    public function getNombreCompletoAttribute()
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }
}
