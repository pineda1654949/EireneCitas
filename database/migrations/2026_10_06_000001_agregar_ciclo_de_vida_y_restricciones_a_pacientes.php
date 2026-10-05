<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * HU-12 / RF-02: ciclo de vida del paciente (Inscripto, En proceso,
 * Finalizado, Cancelado), psicologo asignado por derivacion (RF-05) y
 * unicidad del DNI y del correo a nivel de base de datos (CP-UT-10, CP-UT-11).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->enum('estado_atencion', ['inscripto', 'en_proceso', 'finalizado', 'cancelado'])
                ->default('inscripto')
                ->after('motivo_consulta');
            $table->foreignId('psicologo_id')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();

            $table->dropIndex(['dni']);
            $table->unique('dni');
            $table->unique('correo');
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropUnique(['correo']);
            $table->dropUnique(['dni']);
            $table->index('dni');

            $table->dropConstrainedForeignId('psicologo_id');
            $table->dropColumn('estado_atencion');
        });
    }
};
