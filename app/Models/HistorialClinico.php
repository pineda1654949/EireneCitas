<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialClinico extends Model
{
    use HasFactory;
    protected $table = 'historiales_clinicos';

    protected $fillable = [
        'cita_id',
        'paciente_id',
        'psicologo_id',
        'notas_sesion',
        'avance',
        'documento_path',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function psicologo()
    {
        return $this->belongsTo(User::class, 'psicologo_id');
    }
}
