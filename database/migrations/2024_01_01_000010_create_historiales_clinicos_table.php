<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialesClinicosTable extends Migration
{
    /**
     * Historial clinico por sesion atendida.
     * RF-06: Consulta de historia clinica.
     * Actividad TO-BE 15: "Atender sesion y registrar historial clinico" (Psicologo).
     */
    public function up(): void
    {
        Schema::create('historiales_clinicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('psicologo_id')->constrained('users')->onDelete('cascade');
            $table->text('notas_sesion');
            $table->string('avance')->nullable(); // ej. "Inicial", "En progreso", "Alta"
            $table->string('documento_path')->nullable(); // archivo clinico adjunto
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historiales_clinicos');
    }
}
