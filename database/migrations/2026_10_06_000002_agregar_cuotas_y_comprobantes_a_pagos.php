<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RF-03 / RF-04: promociones que admiten pago en cuotas, numero de cuota de
 * cada pago y voucher adjunto (reemplaza el envio por WhatsApp del AS-IS).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promociones', function (Blueprint $table) {
            $table->boolean('permite_cuotas')->default(false)->after('precio');
            $table->unsignedTinyInteger('max_cuotas')->default(1)->after('permite_cuotas');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->unsignedTinyInteger('numero_cuota')->default(1)->after('metodo_pago');
            $table->unsignedTinyInteger('total_cuotas')->default(1)->after('numero_cuota');
            // Ruta dentro del disco privado: nunca es accesible desde /public.
            $table->string('comprobante_path')->nullable()->after('numero_comprobante');
            $table->string('comprobante_nombre')->nullable()->after('comprobante_path');
            $table->foreignId('registrado_por')->nullable()->after('validado_por')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registrado_por');
            $table->dropColumn(['numero_cuota', 'total_cuotas', 'comprobante_path', 'comprobante_nombre']);
        });

        Schema::table('promociones', function (Blueprint $table) {
            $table->dropColumn(['permite_cuotas', 'max_cuotas']);
        });
    }
};
