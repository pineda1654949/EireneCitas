<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEspecialidadPsicologoTable extends Migration
{
    /**
     * Relacion muchos a muchos: un psicologo puede atender varias
     * especialidades / motivos de consulta.
     */
    public function up(): void
    {
        Schema::create('especialidad_psicologo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especialidad_psicologo');
    }
}
