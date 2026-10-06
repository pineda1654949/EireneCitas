<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Las notas de sesion pasan a guardarse cifradas (cast "encrypted" en
 * HistorialClinico). Esta migracion cifra las notas que ya existian en
 * texto plano. Es idempotente: no vuelve a cifrar notas ya cifradas.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('historiales_clinicos')->orderBy('id')->each(function (object $fila) {
            if ($this->estaCifrado($fila->notas_sesion)) {
                return;
            }

            DB::table('historiales_clinicos')
                ->where('id', $fila->id)
                ->update(['notas_sesion' => Crypt::encryptString($fila->notas_sesion)]);
        });
    }

    public function down(): void
    {
        DB::table('historiales_clinicos')->orderBy('id')->each(function (object $fila) {
            if (! $this->estaCifrado($fila->notas_sesion)) {
                return;
            }

            DB::table('historiales_clinicos')
                ->where('id', $fila->id)
                ->update(['notas_sesion' => Crypt::decryptString($fila->notas_sesion)]);
        });
    }

    private function estaCifrado(string $valor): bool
    {
        try {
            Crypt::decryptString($valor);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }
};
