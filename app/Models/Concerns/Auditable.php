<?php

namespace App\Models\Concerns;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;

/**
 * Registra en la bitacora de auditoria cada creacion, modificacion y
 * eliminacion del modelo. Los campos listados en $noAuditar (contrasenas,
 * tokens, notas clinicas) nunca se copian a la bitacora: solo se indica
 * que cambiaron.
 *
 * @mixin Model
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (self $modelo) {
            Auditoria::registrar('creado', $modelo, null, $modelo->valoresAuditables($modelo->getAttributes()));
        });

        static::updated(function (self $modelo) {
            $cambios = $modelo->getChanges();
            unset($cambios['updated_at']);

            if ($cambios === []) {
                return;
            }

            $anteriores = array_intersect_key($modelo->getRawOriginal(), $cambios);

            Auditoria::registrar(
                'actualizado',
                $modelo,
                $modelo->valoresAuditables($anteriores),
                $modelo->valoresAuditables($cambios),
            );
        });

        static::deleted(function (self $modelo) {
            Auditoria::registrar('eliminado', $modelo, $modelo->valoresAuditables($modelo->getAttributes()), null);
        });
    }

    /**
     * @param  array<string, mixed>  $valores
     * @return array<string, mixed>
     */
    public function valoresAuditables(array $valores): array
    {
        unset($valores['created_at'], $valores['updated_at']);

        foreach ($this->camposNoAuditables() as $campo) {
            if (array_key_exists($campo, $valores)) {
                $valores[$campo] = '[oculto]';
            }
        }

        return $valores;
    }

    /**
     * @return list<string>
     */
    protected function camposNoAuditables(): array
    {
        return array_merge(['password', 'remember_token'], $this->noAuditar ?? []);
    }
}
