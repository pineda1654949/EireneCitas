<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReprogramacionesTable extends Migration
{
    /**
     * Historial de reprogramaciones y cancelaciones de una cita, para
     * trazabilidad (RF-03 y consecuencia detectada en el AS-IS: "riesgo de
     * perdida de historial del paciente").
     */
    public function up()
    {
        Schema::create('reprogramaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->foreignId('realizado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('tipo', ['reprogramacion', 'cancelacion'])->default('reprogramacion');
            $table->date('fecha_anterior')->nullable();
            $table->time('hora_anterior')->nullable();
            $table->date('fecha_nueva')->nullable();
            $table->time('hora_nueva')->nullable();
            $table->text('motivo')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reprogramaciones');
    }
}
