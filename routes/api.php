<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TecnologiaController;
use App\Http\Controllers\Api\AlumnoController;

// Públicas
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register/alumno', [AuthController::class, 'registerAlumno']);

// Protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rutas Admin
    Route::apiResource('tecnologias', TecnologiaController::class);

    // Rutas Alumno
    Route::get('/alumno/perfil', [AlumnoController::class, 'show']);
    Route::put('/alumno/perfil', [AlumnoController::class, 'update']);

    // --- RUTAS DE OFERTAS ---
    // Alumnos (Buscador)
    Route::get('/ofertas', [\App\Http\Controllers\Api\OfertaController::class, 'index']);

    // Empresas
    Route::get('/empresa/ofertas', [\App\Http\Controllers\Api\OfertaController::class, 'misOfertas']);
    Route::post('/empresa/ofertas', [\App\Http\Controllers\Api\OfertaController::class, 'store']);

    // Admins
    Route::get('/admin/ofertas/pendientes', [\App\Http\Controllers\Api\OfertaController::class, 'pendientes']);
    Route::put('/admin/ofertas/{id}/validar', [\App\Http\Controllers\Api\OfertaController::class, 'validar']);
});
