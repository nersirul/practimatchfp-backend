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
     * Dashboard general para el Alumno.
     * 
     * Retorna estadísticas sobre sus candidaturas y una lista
     * de ofertas destacadas para incentivar nuevas solicitudes.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        $practicas = \App\Models\Practica::where('id_alumno', $user->id_alumno)->get();

        $enviadas = $practicas->count();
        $enProceso = $practicas->where('estado', 'SOLICITADA')->count();
        $seleccionado = $practicas->whereIn('estado', ['EN_CURSO', 'FINALIZADA'])->count();

        // Extraer ofertas destacadas recientes (públicas)
        $ofertasDestacadas = \App\Models\Oferta::with('empresa:id_empresa,nombre_comercial,ciudad')
            ->where('estado', 'PUBLICADA')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        return response()->json([
            'stats' => [
                'enviadas' => $enviadas,
                'en_proceso' => $enProceso,
                'seleccionado' => $seleccionado
            ],
            'ofertasDestacadas' => $ofertasDestacadas
        ]);
    }

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
        $alumno = \App\Models\Alumno::with('tecnologias')->findOrFail(Auth::user()->id_alumno);
        return response()->json($alumno);
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
        $user = \App\Models\Alumno::findOrFail(Auth::user()->id_alumno);

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

    // Obtener datos del centro y tutor en tiempo real
    public function infoAcademica()
    {
        // Cogemos al alumno logueado y cargamos mágicamente sus relaciones
        $alumno = \App\Models\Alumno::with(['centro', 'profesor'])->findOrFail(Auth::user()->id_alumno);

        return response()->json([
            'centro' => $alumno->centro,
            'profesor' => $alumno->profesor
        ]);
    }
}
