<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    use HasFactory;
    protected $table = 'promociones';

    protected $fillable = ['nombre', 'descripcion', 'numero_sesiones', 'precio', 'activa'];

    protected $casts = ['activa' => 'boolean'];

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}
