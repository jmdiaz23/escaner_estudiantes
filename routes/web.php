<?php

use App\Http\Controllers\CursoController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScanController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});*/

/*Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');*/

Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Cuando el usuario se loguee exitosamente, Breeze lo manda al '/dashboard'. 
// Lo redirigimos a nuestra sección principal de SICEAP.
Route::get('/dashboard', function () {
    return redirect()->route('estudiantes.index');
})->middleware(['auth', 'verified'])->name('dashboard');

//Route::middleware('auth')->group(function () {
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/estudiantes', [EstudianteController::class, 'index'])->name('estudiantes.index');
Route::post('/estudiantes', [EstudianteController::class, 'store'])->name('estudiantes.store');
Route::put('/estudiantes/{id}', [EstudianteController::class, 'update'])->name('estudiantes.update');
    Route::delete('/estudiantes/{id}', [EstudianteController::class, 'destroy'])->name('estudiantes.destroy');
    // Obtener el QR para verlo en el modal
    Route::get('/estudiantes/{id}/qr', [EstudianteController::class, 'obtenerQr'])->name('estudiantes.qr');
    // Descargar el QR directamente
    Route::get('/estudiantes/{id}/qr/descargar', [EstudianteController::class, 'descargarQr'])->name('estudiantes.qr.descargar');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Rutas de Cursos
    Route::get('/cursos', [CursoController::class, 'index'])->name('cursos.index');
    Route::post('/cursos', [CursoController::class, 'store'])->name('cursos.store');
    Route::put('/cursos/{id}', [CursoController::class, 'update'])->name('cursos.update');
    Route::delete('/cursos/{id}', [CursoController::class, 'destroy'])->name('cursos.destroy');
});


// Rutas del Escáner
Route::get('/escaner', function () {
    //return Inertia\Inertia::render('Escaner/Index');
    return Inertia::render('Escaner/Index');
})->name('escaner.index');

Route::post('/escaner/registrar', [ScanController::class, 'registrar'])->name('escaner.registrar');

require __DIR__.'/auth.php';
