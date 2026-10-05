<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\HorarioFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bloque de atencion semanal de un psicologo (RF-02).
 *
 * @property int $id
 * @property int $psicologo_id
 * @property int $dia_semana
 * @property string $hora_inicio
 * @property string $hora_fin
 * @property bool $activo
 * @property-read User $psicologo
 */
class Horario extends Model
{
    /** @use HasFactory<HorarioFactory> */
    use Auditable, HasFactory;

    protected $fillable = ['psicologo_id', 'dia_semana', 'hora_inicio', 'hora_fin', 'activo'];

    protected $attributes = [
        'activo' => true,
    ];

    protected function casts(): array
    {
        return [
            'dia_semana' => 'integer',
            'activo' => 'boolean',
        ];
    }

    /** 0 = Domingo ... 6 = Sabado (igual que Carbon::dayOfWeek). */
    public const DIAS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miercoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sabado',
        0 => 'Domingo',
    ];

    /**
     * Las horas se guardan siempre como HH:MM:SS para que las comparaciones
     * (cruce de bloques) sean consistentes en MySQL y SQLite.
     *
     * @return Attribute<never, string>
     */
    protected function horaInicio(): Attribute
    {
        return Attribute::set(fn (string $valor) => self::normalizarHora($valor));
    }

    /**
     * @return Attribute<never, string>
     */
    protected function horaFin(): Attribute
    {
        return Attribute::set(fn (string $valor) => self::normalizarHora($valor));
    }

    public static function normalizarHora(string $hora): string
    {
        return strlen($hora) === 5 ? $hora.':00' : $hora;
    }

    public function nombreDia(): string
    {
        return self::DIAS[$this->dia_semana] ?? '-';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function psicologo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'psicologo_id');
    }
}
