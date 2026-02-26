<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// RUTAS PÚBLICAS (No requieren Token)
// ==========================================

// Login único (acepta param 'tipo': 'alumno', 'empresa', 'admin')
Route::post('/login', [AuthController::class, 'login']);

// Registro específico de Alumnos
Route::post('/register/alumno', [AuthController::class, 'registerAlumno']);

// ==========================================
// RUTAS PROTEGIDAS (Requieren Token Bearer)
// ==========================================

Route::middleware('auth:sanctum')->group(function () {

    // Obtener el usuario que ha iniciado sesión
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            // Aquí podríamos devolver el rol si fuera necesario
        ]);
    });

    // Cerrar sesión (Revocar token)
    Route::post('/logout', [AuthController::class, 'logout']);
});
