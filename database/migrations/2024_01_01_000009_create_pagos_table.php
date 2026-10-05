<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagosTable extends Migration
{
    /**
     * Control de pagos por cita (Tabla 1 del documento: "Control de pagos").
     * En el proceso TO-BE el pago se vincula automaticamente a la cita,
     * eliminando la verificacion visual manual del comprobante (AS-IS).
     */
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->decimal('monto', 8, 2);
            $table->enum('metodo_pago', ['tarjeta', 'yape_plin', 'transferencia', 'efectivo'])->default('transferencia');
            $table->enum('estado', ['pendiente', 'confirmado', 'rechazado'])->default('pendiente');
            $table->string('numero_comprobante')->nullable();
            $table->timestamp('fecha_pago')->nullable();
            $table->foreignId('validado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
}
