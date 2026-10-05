<?php

use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\Admin\PacienteController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\PromocionController;
use App\Http\Controllers\Admin\PsicologoController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Api\DisponibilidadController;
use App\Http\Controllers\Auth\RecuperarContrasenaController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\Auth\RestablecerContrasenaController;
use App\Http\Controllers\Auth\SesionController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Psicologo\HistorialController;
use App\Http\Controllers\Psicologo\HorarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas web - Sistema de Gestion de Citas Eirene
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/login');

// ----- Autenticacion y recuperacion de contrasena (RF-07) -----
Route::middleware('guest')->group(function () {
    Route::get('/login', [SesionController::class, 'create'])->name('login');
    Route::post('/login', [SesionController::class, 'store'])->middleware('throttle:formularios-publicos');

    Route::get('/registro', [RegistroController::class, 'create'])->name('register');
    Route::post('/registro', [RegistroController::class, 'store'])->middleware('throttle:formularios-publicos');

    Route::get('/recuperar-contrasena', [RecuperarContrasenaController::class, 'create'])->name('password.request');
    Route::post('/recuperar-contrasena', [RecuperarContrasenaController::class, 'store'])
        ->middleware('throttle:formularios-publicos')
        ->name('password.email');

    Route::get('/restablecer-contrasena/{token}', [RestablecerContrasenaController::class, 'create'])->name('password.reset');
    Route::post('/restablecer-contrasena', [RestablecerContrasenaController::class, 'store'])
        ->middleware('throttle:formularios-publicos')
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [SesionController::class, 'destroy'])->name('logout');

    // ----- Panel principal segun rol -----
    Route::get('/home', HomeController::class)->name('home');

    // ----- Citas: RF-01, RF-03, RF-04 (permisos en CitaPolicy) -----
    Route::controller(CitaController::class)->prefix('citas')->name('citas.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/crear', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{cita}', 'show')->name('show');
        Route::get('/{cita}/reprogramar', 'editarFecha')->name('reprogramar.form');
        Route::put('/{cita}/reprogramar', 'reprogramar')->name('reprogramar');
        Route::get('/{cita}/cancelar', 'confirmarCancelacion')->name('cancelar.form');
        Route::put('/{cita}/cancelar', 'cancelar')->name('cancelar');
        Route::put('/{cita}/confirmar', 'confirmar')->name('confirmar');
    });

    // ----- API interna (AJAX) de disponibilidad en tiempo real: RF-02 -----
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/especialidades/{especialidad}/psicologos', [DisponibilidadController::class, 'psicologosPorEspecialidad'])
            ->name('especialidades.psicologos');
        Route::get('/horas-disponibles', [DisponibilidadController::class, 'horasDisponibles'])
            ->name('horas-disponibles');
    });

    // ----- Pagos, pacientes y reportes: recepcionista y administrador -----
    Route::middleware('rol:recepcionista,administrador')->group(function () {
        Route::get('/citas/{cita}/pagos/registrar', [PagoController::class, 'create'])->name('pagos.create');
        Route::post('/citas/{cita}/pagos', [PagoController::class, 'store'])->name('pagos.store');
        Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
        Route::put('/pagos/{pago}/validar', [PagoController::class, 'validar'])->name('pagos.validar');
        Route::put('/pagos/{pago}/rechazar', [PagoController::class, 'rechazar'])->name('pagos.rechazar');

        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index'); // RF-08
        Route::get('/reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');

        // RF-05 (Tabla 8 del documento)
        Route::resource('admin/pacientes', PacienteController::class)
            ->except(['show'])
            ->names('admin.pacientes');
    });

    // ----- Administrador: psicologos, promociones y auditoria -----
    Route::middleware('rol:administrador')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('psicologos', PsicologoController::class)->except(['show']);
        Route::resource('promociones', PromocionController::class)
            ->except(['show'])
            ->parameters(['promociones' => 'promocion']);
        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
    });

    // ----- Psicologo: disponibilidad e historia clinica (RF-06) -----
    Route::middleware('rol:psicologo')->prefix('psicologo')->name('psicologo.')->group(function () {
        Route::get('/horarios', [HorarioController::class, 'index'])->name('horarios.index');
        Route::post('/horarios', [HorarioController::class, 'store'])->name('horarios.store');
        Route::patch('/horarios/{horario}/alternar', [HorarioController::class, 'alternar'])->name('horarios.alternar');
        Route::delete('/horarios/{horario}', [HorarioController::class, 'destroy'])->name('horarios.destroy');

        Route::get('/pacientes/{paciente}/historial', [HistorialController::class, 'porPaciente'])->name('historial.paciente');
        Route::get('/citas/{cita}/historial/crear', [HistorialController::class, 'create'])->name('historial.create');
        Route::post('/citas/{cita}/historial', [HistorialController::class, 'store'])->name('historial.store');
    });
});
