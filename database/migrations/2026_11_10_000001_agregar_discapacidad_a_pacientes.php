<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Accesibilidad: registra si el paciente tiene discapacidad, el tipo y
 * necesidades especiales para que la recepcion/psicologo adapten la atencion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->boolean('tiene_discapacidad')->default(false)->after('estado_atencion');
            $table->enum('tipo_discapacidad', ['fisica', 'visual', 'auditiva', 'intelectual', 'psicosocial', 'otra'])
                ->nullable()->after('tiene_discapacidad');
            $table->text('necesidades_especiales')->nullable()->after('tipo_discapacidad');
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn(['tiene_discapacidad', 'tipo_discapacidad', 'necesidades_especiales']);
        });
    }
};
