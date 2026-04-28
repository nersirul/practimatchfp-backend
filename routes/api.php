<?php

/**
 * Rutas de la API de PractiMatch FP
 * * Este archivo agrupa y expone todos los endpoints REST consumidos por Frontend (React).
 * Todo este grupo aplica automáticamente el Middleware 'api'.
 * Las rutas protegidas validan el Bearer Token a través de 'auth:sanctum'.
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TecnologiaController;
use App\Http\Controllers\Api\AlumnoController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS (No requieren token)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/registro', [\App\Http\Controllers\Api\AuthController::class, 'registro']);
Route::post('/register/alumno', [AuthController::class, 'registerAlumno']);
Route::get('/centros', function () {
    return response()->json(\App\Models\Centro::orderBy('nombre')->get());
});

// Exposición del inventario público de descripciones de oferta
Route::get('/ofertas', [\App\Http\Controllers\Api\OfertaController::class, 'index']);

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS (Requieren Bearer Token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Endpoint general para obtener el usuario activo de la sesión
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Invalida el token actual
    Route::post('/logout', [AuthController::class, 'logout']);

    /**
     * MÓDULO ROL: ADMINISTRADOR
     */
    Route::get('/admin/dashboard', [\App\Http\Controllers\Api\AdminUserController::class, 'dashboard']);
    Route::apiResource('tecnologias', TecnologiaController::class);
    Route::get('/admin/empresas/pendientes', [\App\Http\Controllers\Api\AdminUserController::class, 'empresasPendientes']);
    Route::put('/admin/empresas/{id}/validar', [\App\Http\Controllers\Api\AdminUserController::class, 'validarEmpresa']);
    Route::get('/admin/usuarios/{tipo}', [\App\Http\Controllers\Api\AdminUserController::class, 'index']);
    Route::put('/admin/usuarios/{tipo}/{id}', [\App\Http\Controllers\Api\AdminUserController::class, 'update']);
    Route::delete('/admin/usuarios/{tipo}/{id}', [\App\Http\Controllers\Api\AdminUserController::class, 'destroy']);

    /**
     * MÓDULO ROL: ALUMNO
     */
    Route::get('/alumno/dashboard', [AlumnoController::class, 'dashboard']);
    Route::get('/alumno/perfil', [AlumnoController::class, 'show']);
    Route::put('/alumno/perfil', [AlumnoController::class, 'update']);
    Route::get('/alumno/candidaturas', [\App\Http\Controllers\Api\PracticaController::class, 'misCandidaturas']);
    Route::get('/alumno/info-academica', [\App\Http\Controllers\Api\AlumnoController::class, 'infoAcademica']);
    Route::post('/alumno/practicas/{id_practica}/valorar', [\App\Http\Controllers\Api\PracticaController::class, 'valorarEmpresa']);

    /**
     * MÓDULO CORE: OFERTAS Y CANDIDATURAS (MATCH)
     */

    // El DETALLE de la oferta sigue protegido para Alumnos y resto de roles logueados
    Route::get('/ofertas/{id}', [\App\Http\Controllers\Api\OfertaController::class, 'show']);
    // Aplicar a vacante (El Alumno hace 'Click en Solicitar Práctica')
    Route::post('/ofertas/{id_oferta}/solicitar', [\App\Http\Controllers\Api\PracticaController::class, 'solicitar']);
    // RUTA COMPARTIDA (Para que la Empresa edite sus ofertas o el Admin edite cualquiera)
    Route::put('/ofertas/{id}/editar', [\App\Http\Controllers\Api\OfertaController::class, 'update']);

    /**
     * MÓDULO ROL: EMPRESA
     */
    Route::get('/empresa/perfil', [\App\Http\Controllers\Api\EmpresaController::class, 'show']);
    Route::put('/empresa/perfil', [\App\Http\Controllers\Api\EmpresaController::class, 'update']);
    Route::get('/empresa/ofertas', [\App\Http\Controllers\Api\OfertaController::class, 'misOfertas']);
    Route::post('/empresa/ofertas', [\App\Http\Controllers\Api\OfertaController::class, 'store']);
    Route::get('/empresa/ofertas/{id_oferta}/candidatos', [\App\Http\Controllers\Api\PracticaController::class, 'candidatosPorOferta']);
    Route::put('/empresa/ofertas/{id_oferta}/toggle', [\App\Http\Controllers\Api\OfertaController::class, 'toggleActiva']);
    Route::put('/empresa/ofertas/{id_oferta}/cerrar', [\App\Http\Controllers\Api\OfertaController::class, 'cerrar']);
    Route::put('/empresa/practicas/{id_practica}/estado', [\App\Http\Controllers\Api\PracticaController::class, 'actualizarEstado']);

    /**
     * APROBACIÓN DE OFERTAS (Admin)
     */
    Route::get('/admin/ofertas/pendientes', [\App\Http\Controllers\Api\OfertaController::class, 'pendientes']);
    Route::put('/admin/ofertas/{id}/validar', [\App\Http\Controllers\Api\OfertaController::class, 'validar']);

    /**
     * MÓDULO ROL: PROFESOR (SUPERVISOR)
     */
    Route::get('/profesor/practicas', [\App\Http\Controllers\Api\ProfesorController::class, 'practicas']);
    Route::post('/profesor/practicas/{id_practica}/evaluar', [\App\Http\Controllers\Api\ProfesorController::class, 'evaluar']);
    Route::get('/profesor/alumnos/huérfanos', [\App\Http\Controllers\Api\ProfesorController::class, 'alumnosSinTutor']);
    Route::get('/profesor/mis-alumnos', [\App\Http\Controllers\Api\ProfesorController::class, 'misAlumnos']);
    Route::post('/profesor/alumnos/{id_alumno}/tutoria', [\App\Http\Controllers\Api\ProfesorController::class, 'gestionarTutoria']);
    Route::post('/profesor/practicas/{id_practica}/aprobar', [\App\Http\Controllers\Api\ProfesorController::class, 'aprobarInicioPractica']);
    Route::get('/profesor/perfil', function () {
        $profesor = \App\Models\Profesor::findOrFail(Auth::user()->id_profesor);
        return response()->json($profesor);
    });
    Route::put('/profesor/perfil', [\App\Http\Controllers\Api\ProfesorController::class, 'updatePerfil']);

    /**
     * EXPORTACIÓN DE DOCUMENTOS (Generador DOMPDF)
     * Abierto tanto para Empresa, Profesor y Alumno (cada quien ve su FCT).
     */
    Route::get('/practicas/{id_practica}/pdf', [\App\Http\Controllers\Api\ProfesorController::class, 'descargarPDF']);
    
    Route::get('/cmd/storage-link', function () {
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        return 'Enlace de almacenamiento creado con éxito.';
    });
});
