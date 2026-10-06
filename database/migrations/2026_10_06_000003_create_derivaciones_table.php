<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RF-05: derivacion de pacientes. La administracion deriva al paciente a un
 * psicologo segun sus sintomas y el psicologo acepta o rechaza con motivo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('derivaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('psicologo_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('especialidad_id')->nullable()->constrained('especialidades')->nullOnDelete();
            $table->foreignId('derivado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['pendiente', 'aceptada', 'rechazada'])->default('pendiente');
            $table->text('motivo_rechazo')->nullable();
            $table->timestamp('respondida_at')->nullable();
            $table->timestamps();

            $table->index(['psicologo_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('derivaciones');
    }
};
