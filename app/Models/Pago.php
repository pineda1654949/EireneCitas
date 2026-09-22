<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'cita_id',
        'monto',
        'metodo_pago',
        'estado',
        'numero_comprobante',
        'fecha_pago',
        'validado_por',
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function validadoPor()
    {
        return $this->belongsTo(User::class, 'validado_por');
    }
}
