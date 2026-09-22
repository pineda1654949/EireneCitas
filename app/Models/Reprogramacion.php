<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reprogramacion extends Model
{
    use HasFactory;
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

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'realizado_por');
    }
}
