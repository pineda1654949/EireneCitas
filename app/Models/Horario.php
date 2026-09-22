<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = ['psicologo_id', 'dia_semana', 'hora_inicio', 'hora_fin', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public const DIAS = [
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miercoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sabado',
    ];

    public function psicologo()
    {
        return $this->belongsTo(User::class, 'psicologo_id');
    }
}
