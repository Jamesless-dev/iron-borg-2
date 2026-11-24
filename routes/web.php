<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MiembroController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\HistorialController;

// ============================================================
// 1. Raíz
// ============================================================
Route::get('/', fn () => redirect()->route('login'));

// ============================================================
// 2. Públicas
// ============================================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ============================================================
// 3. Protegidas (UN SOLO grupo auth + check.inactivity)
// ============================================================
Route::middleware(['auth', 'check.inactivity'])->group(function () {

    // --- Dashboard (P-02) ---
    Route::get('/dashboard', function () {
        $usuario = auth()->user();
        return redirect()->route($usuario->rol === 'Administrador' ? 'admin.dashboard' : 'recepcion.dashboard');
    })->name('dashboard');

    // Estas dos rutas SÍ usan el controlador que calcula los datos
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/recepcion/dashboard', [DashboardController::class, 'index'])->name('recepcion.dashboard');

    // --- Miembros ---
    Route::get('/miembros', [MiembroController::class, 'index'])->name('miembros.index');
    Route::post('/miembros', [MiembroController::class, 'store'])->name('miembros.store');
    Route::put('/miembros/{idMiembro}', [MiembroController::class, 'update'])->name('miembros.update');
    Route::get('/miembros/verificar-dni', [MiembroController::class, 'verificarDni'])->name('miembros.verificarDni');
    Route::get('/miembros/buscar', [MiembroController::class, 'buscar'])->name('miembros.buscar');
    Route::get('/miembros/nuevo', fn () => redirect()->route('miembros.index'))->name('miembros.create');

    // --- Membresías ---
    Route::get('/membresias', [MembresiaController::class, 'index'])->name('membresias.index');
    Route::get('/membresias/buscar', [MembresiaController::class, 'buscar'])->name('membresias.buscar');
    Route::post('/membresias/{id}/renovar', [MembresiaController::class, 'renovar'])->name('membresias.renovar');
    Route::post('/membresias/{id}/cancelar', [MembresiaController::class, 'cancelar'])->name('membresias.cancelar');

    // --- Asistencias ---
    Route::get('/asistencias', [AsistenciaController::class, 'index'])->name('asistencias.index');
    Route::get('/asistencias/dia', [AsistenciaController::class, 'dia'])->name('asistencias.dia');
    Route::get('/asistencias/identificar', [AsistenciaController::class, 'identificar'])->name('asistencias.identificar');
    Route::post('/asistencias/registrar', [AsistenciaController::class, 'registrar'])->name('asistencias.registrar');
    Route::post('/asistencias/{idAsistencia}/salida', [AsistenciaController::class, 'registrarSalida'])->name('asistencias.salida');

    // --- Pagos ---
    Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::get('/pagos/buscar', [PagoController::class, 'buscar'])->name('pagos.buscar');
    Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');
    Route::get('/pagos/comprobante/{numero}', [PagoController::class, 'comprobante'])->name('pagos.comprobante');
    Route::get('/pagos/comprobante/{numero}/imprimir', [PagoController::class, 'imprimir'])->name('pagos.comprobante.imprimir');
    Route::get('/pagos/nuevo', fn () => redirect()->route('pagos.index'))->name('pagos.create');

    // --- Reportes ---
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::post('/reportes/generar', [ReporteController::class, 'generar'])->name('reportes.generar');

    // --- Historial ---
    Route::get('/historial', [HistorialController::class, 'index'])->name('historial.index');

    // --- Configuración ---
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::post('/configuracion/planes', [ConfiguracionController::class, 'storePlan'])->name('configuracion.storePlan');
    Route::put('/configuracion/planes/{idPlan}', [ConfiguracionController::class, 'updatePlan'])->name('configuracion.updatePlan');
    Route::post('/configuracion/planes/{idPlan}/toggle', [ConfiguracionController::class, 'togglePlan'])->name('configuracion.togglePlan');
    Route::post('/configuracion/parametros', [ConfiguracionController::class, 'updateParametros'])->name('configuracion.updateParametros');
});