<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Tabla de usuarios del sistema.
     * RF-07: Control de acceso y autenticacion de usuarios.
     * Roles definidos en el proyecto (Tabla 7 del documento):
     *   administrador, recepcionista, psicologo, paciente
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('apellidos')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('dni', 15)->nullable()->unique();
            $table->string('telefono', 20)->nullable();
            $table->enum('role', ['administrador', 'recepcionista', 'psicologo', 'paciente'])
                ->default('paciente');
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
