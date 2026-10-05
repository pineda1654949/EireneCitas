<?php

/*
|--------------------------------------------------------------------------
| Parametros de negocio de la clinica Eirene
|--------------------------------------------------------------------------
| Centraliza las reglas configurables para no tenerlas repartidas en el
| codigo. Se pueden ajustar por entorno desde el archivo .env.
*/

return [

    'clinica' => [
        'nombre' => env('CLINICA_NOMBRE', 'Clínica Psicológica Eirene'),
        'telefono' => env('CLINICA_TELEFONO'),
        'correo' => env('CLINICA_CORREO', env('MAIL_FROM_ADDRESS')),
    ],

    'citas' => [
        // RNF "Confiabilidad de reglas de negocio": maximo de reprogramaciones.
        'max_reprogramaciones' => (int) env('CITAS_MAX_REPROGRAMACIONES', 3),

        // Duracion de cada sesion y separacion entre inicios de sesion (minutos).
        'duracion_minutos' => (int) env('CITAS_DURACION_MINUTOS', 50),
        'intervalo_minutos' => (int) env('CITAS_INTERVALO_MINUTOS', 60),

        // Anticipacion minima para reservar una hora del mismo dia (minutos).
        'anticipacion_minima_minutos' => (int) env('CITAS_ANTICIPACION_MINUTOS', 60),
    ],

    // Proxies de confianza (p. ej. "*" detras de Cloudflare o un balanceador),
    // necesario para detectar HTTPS y la IP real del visitante.
    'proxies_confiables' => env('TRUSTED_PROXIES'),

    'auditoria' => [
        'dias_retencion' => (int) env('AUDITORIA_DIAS_RETENCION', 365),
    ],

];
