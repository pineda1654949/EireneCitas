<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHorariosTable extends Migration
{
    /**
     * Disponibilidad horaria configurada por cada psicologo.
     * RF-02: Consulta de disponibilidad de horarios (en tiempo real, ya no
     * mediante Excel mensual como en el proceso AS-IS).
     */
    public function up()
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psicologo_id')->constrained('users')->onDelete('cascade');
            // 0 = Domingo ... 6 = Sabado
            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('horarios');
    }
}
