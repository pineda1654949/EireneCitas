<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use HasFactory;
    protected $table = 'especialidades';

    protected $fillable = ['nombre', 'descripcion'];

    public function psicologos()
    {
        return $this->belongsToMany(User::class, 'especialidad_psicologo', 'especialidad_id', 'user_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}
