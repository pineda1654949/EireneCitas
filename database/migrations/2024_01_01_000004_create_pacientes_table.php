<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePacientesTable extends Migration
{
    /**
     * Ficha del paciente (datos clinicos/personales adicionales a "users").
     * RF-05: Registro y actualizacion de datos del paciente.
     */
    public function up()
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('dni', 15)->nullable();
            $table->unsignedTinyInteger('edad')->nullable();
            $table->string('correo')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('direccion')->nullable();
            $table->text('motivo_consulta')->nullable(); // sintomas reportados (formulario)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pacientes');
    }
}
