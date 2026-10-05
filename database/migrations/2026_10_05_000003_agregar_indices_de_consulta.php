<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indices para las consultas mas frecuentes: agenda del psicologo por fecha,
 * filtros por estado en listados y reportes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->index(['psicologo_id', 'fecha', 'hora'], 'citas_agenda_index');
            $table->index(['fecha', 'estado'], 'citas_fecha_estado_index');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->index('estado');
        });

        Schema::table('horarios', function (Blueprint $table) {
            $table->index(['psicologo_id', 'dia_semana', 'activo'], 'horarios_busqueda_index');
        });

        Schema::table('pacientes', function (Blueprint $table) {
            $table->index('dni');
            $table->index('apellidos');
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropIndex(['dni']);
            $table->dropIndex(['apellidos']);
        });

        Schema::table('horarios', function (Blueprint $table) {
            $table->dropIndex('horarios_busqueda_index');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropIndex(['estado']);
        });

        Schema::table('citas', function (Blueprint $table) {
            $table->dropIndex('citas_agenda_index');
            $table->dropIndex('citas_fecha_estado_index');
        });
    }
};
