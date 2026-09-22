<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    /**
     * Regla de negocio (RNF "Confiabilidad de reglas de negocio" del documento):
     * limite maximo de 3 reprogramaciones por cita.
     */
    public const MAX_REPROGRAMACIONES = 3;

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

    protected $casts = [
        'fecha' => 'date',
    ];

    // ----- Relaciones -----
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function psicologo()
    {
        return $this->belongsTo(User::class, 'psicologo_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function promocion()
    {
        return $this->belongsTo(Promocion::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function reprogramaciones()
    {
        return $this->hasMany(Reprogramacion::class);
    }

    public function historialClinico()
    {
        return $this->hasOne(HistorialClinico::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // ----- Reglas de negocio -----
    public function puedeReprogramarse(): bool
    {
        return $this->numero_reprogramaciones < self::MAX_REPROGRAMACIONES
            && !in_array($this->estado, ['cancelada', 'atendida']);
    }

    public function pagoConfirmado(): bool
    {
        return $this->pagos()->where('estado', 'confirmado')->exists();
    }

    public function getEstadoBadgeAttribute()
    {
        return [
            'pendiente' => 'warning',
            'confirmada' => 'success',
            'atendida' => 'primary',
            'cancelada' => 'danger',
            'reprogramada' => 'info',
        ][$this->estado] ?? 'secondary';
    }
}
