<?php

use App\Models\Auditoria;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Tareas programadas
|--------------------------------------------------------------------------
| En el servidor basta UNA tarea cron que ejecute cada minuto:
|   * * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
| (ver docs/DESPLIEGUE.md)
*/

// Procesa los correos en cola. Apto para hosting compartido: no necesita
// un proceso permanente (supervisor); termina cuando la cola queda vacia.
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

// Recordatorio de las citas del dia siguiente.
Schedule::command('citas:enviar-recordatorios')->dailyAt('08:00')->onOneServer();

// Respaldos de base de datos y archivos (spatie/laravel-backup).
Schedule::command('backup:clean')->dailyAt('01:00')->onOneServer();
Schedule::command('backup:run --only-db')->dailyAt('01:30')->onOneServer();
Schedule::command('backup:monitor')->dailyAt('03:00')->onOneServer();

// Depura registros de auditoria mas antiguos que el periodo de retencion.
Schedule::command('model:prune', ['--model' => [Auditoria::class]])->daily();
