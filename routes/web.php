<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RifaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PremioController;
use App\Http\Controllers\ParticipanteController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| ROOT → DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |----------------------------------------------------------------------
    | ADMIN & SUPER ADMIN
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin,super_admin')->group(function () {

        // 🎰 Rifa
        Route::get('/rifa', [RifaController::class, 'index'])->name('rifa.index');
        Route::post('/rifa/nombre', [RifaController::class, 'girarNombre'])->name('rifa.girarNombre');
        Route::post('/rifa/premio', [RifaController::class, 'girarPremio'])->name('rifa.girarPremio');

        // 🎁 Premios
        Route::get('/premios', [PremioController::class, 'index'])->name('premios.index');
        Route::post('/premios/importar', [PremioController::class, 'importar'])->name('premios.importar');
        Route::delete('/premios/borrar-todo', [PremioController::class, 'borrarTodo'])
            ->name('premios.borrarTodo');

        // 👥 Participantes
        Route::get('/participantes', [ParticipanteController::class, 'index'])
            ->name('participantes.index');
        Route::post('/participantes/importar', [ParticipanteController::class, 'importar'])
            ->name('participantes.importar');
        Route::delete('/participantes/borrar-todo', [ParticipanteController::class, 'borrarTodo'])
            ->name('participantes.borrarTodo');
            Route::get('/rifa/estadisticas', [RifaController::class, 'estadisticas'])
    ->name('rifa.estadisticas');

    });

    /*
    |----------------------------------------------------------------------
    | PARTICIPANTES – BUSCAR (AJAX)
    |----------------------------------------------------------------------
    */
    Route::get('/participantes/buscar', [ParticipanteController::class, 'buscar'])
        ->name('participantes.buscar');

    /*
    |----------------------------------------------------------------------
    | SUPER ADMIN
    |----------------------------------------------------------------------
    */
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
            ->name('admin.dashboard');
    });
});

require __DIR__.'/auth.php';
