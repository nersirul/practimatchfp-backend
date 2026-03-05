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
});
