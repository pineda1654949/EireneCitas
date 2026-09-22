<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEspecialidadesTable extends Migration
{
    /**
     * Especialidades / areas de atencion psicologica, usadas para
     * derivar al paciente al psicologo adecuado segun sus sintomas
     * (Capitulo 2.2 y 3.4 del documento).
     */
    public function up()
    {
        Schema::create('especialidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('especialidades');
    }
}
