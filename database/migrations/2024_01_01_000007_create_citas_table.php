<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitasTable extends Migration
{
    /**
     * Tabla principal de citas.
     * RF-01: Registro de citas.
     * RF-03: Reprogramacion y cancelacion de citas (limite de 3 reprogramaciones,
     *        ver RNF "Confiabilidad de reglas de negocio" del documento).
     * RF-04: Confirmacion de citas.
     */
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('psicologo_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('especialidad_id')->nullable()->constrained('especialidades')->nullOnDelete();
            $table->foreignId('promocion_id')->nullable()->constrained('promociones')->nullOnDelete();
            $table->date('fecha');
            $table->time('hora');
            $table->text('motivo_consulta')->nullable();
            $table->enum('estado', [
                'pendiente',    // creada, a la espera de pago/confirmacion
                'confirmada',   // pago validado y cita confirmada (RF-04)
                'atendida',     // sesion realizada, con historial clinico
                'cancelada',
                'reprogramada', // fue movida a una nueva fecha/hora
            ])->default('pendiente');
            $table->unsignedTinyInteger('numero_reprogramaciones')->default(0); // maximo 3
            $table->string('enlace_meet')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
}
