<?php

namespace App\Models;

use Database\Factories\EspecialidadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Area de atencion usada para derivar al paciente al psicologo adecuado.
 *
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 */
class Especialidad extends Model
{
    /** @use HasFactory<EspecialidadFactory> */
    use HasFactory;

    protected $table = 'especialidades';

    protected $fillable = ['nombre', 'descripcion'];

    /**
     * @return BelongsToMany<User, $this>
     */
    public function psicologos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'especialidad_psicologo', 'especialidad_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Cita, $this>
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
}
