<?php

use App\Http\Controllers\Admin\PacienteController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\PromocionController;
use App\Http\Controllers\Admin\PsicologoController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Api\DisponibilidadController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Psicologo\HistorialController;
use App\Http\Controllers\Psicologo\HorarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistema de Gestion de Citas Eirene
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('login'));

// ----- Autenticacion (RF-07) -----
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/registro', [AuthController::class, 'mostrarRegistro'])->name('register');
    Route::post('/registro', [AuthController::class, 'registro']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ----- Panel principal (redirige segun rol) -----
Route::get('/home', [HomeController::class, 'index'])->middleware('auth')->name('home');

// ----- Citas: RF-01, RF-02, RF-03, RF-04 (paciente, recepcionista, psicologo, admin) -----
Route::middleware('auth')->group(function () {
    Route::get('/citas/crear', [CitaController::class, 'create'])->name('citas.create');
    Route::post('/citas', [CitaController::class, 'store'])->name('citas.store');
    Route::get('/citas', [CitaController::class, 'index'])->name('citas.index');
    Route::get('/citas/{cita}', [CitaController::class, 'show'])->name('citas.show');

    Route::get('/citas/{cita}/reprogramar', [CitaController::class, 'formReprogramar'])->name('citas.reprogramar.form');
    Route::put('/citas/{cita}/reprogramar', [CitaController::class, 'reprogramar'])->name('citas.reprogramar');

    Route::get('/citas/{cita}/cancelar', [CitaController::class, 'formCancelar'])->name('citas.cancelar.form');
    Route::put('/citas/{cita}/cancelar', [CitaController::class, 'cancelar'])->name('citas.cancelar');
});

// ----- Confirmacion y pagos: recepcionista / administrador -----
Route::middleware(['auth', 'role:recepcionista,administrador'])->group(function () {
    Route::put('/citas/{cita}/confirmar', [CitaController::class, 'confirm'])->name('citas.confirmar');

    Route::get('/citas/{cita}/pagos/registrar', [PagoController::class, 'create'])->name('pagos.create');
    Route::post('/citas/{cita}/pagos', [PagoController::class, 'store'])->name('pagos.store');
    Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::put('/pagos/{pago}/validar', [PagoController::class, 'validar'])->name('pagos.validar');
    Route::put('/pagos/{pago}/rechazar', [PagoController::class, 'rechazar'])->name('pagos.rechazar');

    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index'); // RF-08
});

// ----- Administrador: psicologos, promociones -----
Route::middleware(['auth', 'role:administrador'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('psicologos', PsicologoController::class)->except(['show']);
    Route::resource('promociones', PromocionController::class)->except(['show']);
});

// ----- Pacientes: administrador y recepcionista (RF-05, Tabla 8 del documento) -----
Route::middleware(['auth', 'role:administrador,recepcionista'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('pacientes', PacienteController::class)->except(['show']);
});

// ----- Psicologo: disponibilidad e historia clinica (RF-06) -----
Route::middleware(['auth', 'role:psicologo'])->prefix('psicologo')->name('psicologo.')->group(function () {
    Route::get('/horarios', [HorarioController::class, 'index'])->name('horarios.index');
    Route::post('/horarios', [HorarioController::class, 'store'])->name('horarios.store');
    Route::delete('/horarios/{horario}', [HorarioController::class, 'destroy'])->name('horarios.destroy');

    Route::get('/pacientes/{pacienteId}/historial', [HistorialController::class, 'porPaciente'])->name('historial.paciente');
    Route::get('/citas/{cita}/historial/crear', [HistorialController::class, 'create'])->name('historial.create');
    Route::post('/citas/{cita}/historial', [HistorialController::class, 'store'])->name('historial.store');
});

// ----- API interna (AJAX) para disponibilidad en tiempo real: RF-02 -----
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/especialidades/{especialidad}/psicologos', [DisponibilidadController::class, 'psicologosPorEspecialidad']);
    Route::get('/horas-disponibles', [DisponibilidadController::class, 'horasDisponibles']);
});
