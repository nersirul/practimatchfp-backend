<?php

/**
 * Controlador de API - AlumnoController
 * 
 * Gestiona el perfil público y privado del estudiante.
 * Incluye endopoints para consultar el perfil actual y editar sus datos personales,
 * así como sincronizar el catálogo de tecnologías que el alumno domina.
 * 
 * @package App\Http\Controllers\Api
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlumnoController extends Controller
{
    /**
     * Muestra el perfil del alumno autenticado.
     * 
     * Retorna el recurso del alumno logueado acompañado por medio de eager loading
     * de sus tecnologías favoritas/estudiadas.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        return response()->json(Auth::user()->load('tecnologias'));
    }

    /**
     * Actualizar perfil general del alumno y sus habilidades.
     * 
     * Parsea la Request validando nombre, apellidos y ciclo, y en un segundo
     * paso, usa Eloquent para sincronizar `sync()` la tabla pivote de tecnologías,
     * reemplazando las anteriores con las nuevas recibidas desde el frontend.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. Validar los datos básicos provenientes del formulario de React (PerfilAlumno.jsx)
        $request->validate([
            'nombre' => 'required',
            'apellidos' => 'required',
            'ciclo' => 'required',
            'modalidad_preferida' => 'required',
        ]);

        $user->update($request->only(['nombre', 'apellidos', 'ciclo', 'modalidad_preferida']));

        // 2. Sincronización de Tecnologías (Relación N:M)
        // Construimos el array especial que prepare Eloquent para insertar
        // el id de la tecnología como clave y sus columnas pivot como valores.
        if ($request->has('tecnologias')) {
            $syncData = [];
            foreach ($request->input('tecnologias') as $tec) {
                $syncData[$tec['id_tecnologia']] = [
                    'nivel' => $tec['nivel'] ?? 1,
                    'tipo_relacion' => $tec['tipo_relacion'] ?? 'INTERES'
                ];
            }
            // Utilizamos sync() para borrar las antiguas que desmarcó e insertar las nuevas/mantenidas
            $user->tecnologias()->sync($syncData);
        }

        return response()->json(['message' => 'Perfil actualizado', 'user' => $user->load('tecnologias')]);
    }
}
