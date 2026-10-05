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

    /**
     * En MySQL, al crear un indice compuesto que empieza por la columna de una
     * llave foranea, el motor elimina el indice simple que habia creado para
     * esa FK y pasa a usar el compuesto. Por eso, antes de borrar los
     * compuestos se recrea el indice simple de psicologo_id (DEF-007).
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->index('psicologo_id', 'citas_psicologo_id_foreign');
            $table->dropIndex('citas_agenda_index');
            $table->dropIndex('citas_fecha_estado_index');
        });

        Schema::table('horarios', function (Blueprint $table) {
            $table->index('psicologo_id', 'horarios_psicologo_id_foreign');
            $table->dropIndex('horarios_busqueda_index');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropIndex(['estado']);
        });

        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropIndex(['dni']);
            $table->dropIndex(['apellidos']);
        });
    }
};
