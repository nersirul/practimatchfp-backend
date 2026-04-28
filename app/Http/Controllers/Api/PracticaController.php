<?php

/**
 * Controlador de API - PracticaController
 * 
 * Es el motor principal del "Match". Permite a los alumnos aplicar a ofertas 
 * y a las empresas visualizar y gestionar dichas candidaturas (filtrar, descartar o admitir).
 * 
 * @package App\Http\Controllers\Api
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Practica;
use App\Models\Oferta;
use Illuminate\Support\Facades\Auth;

class PracticaController extends Controller
{
    /**
     * Crear Candidatura (Match).
     * 
     * Invocado cuando un Alumno pulsa en "Solicitar Práctica" desde una oferta.
     * Verifica que el usuario sea verdaderamente alumno y que no haya enviado 
     * ya la misma solicitud antes.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id_oferta
     * @return \Illuminate\Http\JsonResponse
     */
    public function solicitar(Request $request, $id_oferta)
    {
        $user = Auth::user();
        if (!$user->id_alumno) return response()->json(['error' => 'Solo alumnos'], 403);

        // Restricción de duplicidad: Prevención de candidaturas múltiples a un mismo puesto
        $existe = Practica::where('id_alumno', $user->id_alumno)
            ->where('id_oferta', $id_oferta)
            ->exists();

        if ($existe) {
            return response()->json(['error' => 'Ya has solicitado esta oferta'], 422);
        }

        $practica = Practica::create([
            'id_alumno' => $user->id_alumno,
            'id_oferta' => $id_oferta,
            'estado' => 'SOLICITADA'
            // La asignación del ID del profesor evaluador se delega a la fase de supervisión
        ]);

        return response()->json(['message' => 'Solicitud enviada con éxito', 'practica' => $practica], 201);
    }

    /**
     * Bandeja del Alumno: Historial de Solicitudes.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function misCandidaturas()
    {
        $user = Auth::user();

        $practicas = Practica::with(['oferta.empresa'])
            ->where('id_alumno', $user->id_alumno)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($practicas);
    }

    /**
     * Bandeja de Empresa: Listar postulantes a una Oferta.
     * 
     * Muestra a los reclutadores el talento pre-seleccionado, verificando por seguridad
     * que la empresa que hace la petición es efectivamente la creadora original de la oferta.
     * 
     * @param int $id_oferta
     * @return \Illuminate\Http\JsonResponse
     */
    public function candidatosPorOferta($id_oferta)
    {
        $user = Auth::user();

        // Restricción de Autorización Horizontal
        $oferta = Oferta::findOrFail($id_oferta);
        if ($oferta->id_empresa !== $user->id_empresa) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Extracción Eager-Loaded de los currículos de los candidatos
        $candidaturas = Practica::with(['alumno.tecnologias'])
            ->where('id_oferta', $id_oferta)
            ->get();

        return response()->json($candidaturas);
    }

    /**
     * Workflow de Admisión: Empresa acepta o rechaza a un alumno.
     * 
     * Transita el modelo "Practica" entre sus estados posibles (e.g. EN_CURSO, RECHAZADA)
     * basándose en las determinaciones de RR.HH.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id_practica
     * @return \Illuminate\Http\JsonResponse
     */
    public function actualizarEstado(Request $request, $id_practica)
    {
        $user = Auth::user();

        $request->validate([
            'estado' => 'required|in:ESPERANDO_TUTOR,RECHAZADA'
        ]);

        $practica = Practica::with('oferta')->findOrFail($id_practica);

        if ($request->estado === 'ESPERANDO_TUTOR' && $practica->oferta->vacantes !== null) {
            // Cuantificación de plazas activas para prevenir desbordamiento de vacantes
            $plazasOcupadas = Practica::where('id_oferta', $practica->id_oferta)
                ->whereIn('estado', ['ESPERANDO_TUTOR', 'EN_CURSO', 'FINALIZADA'])
                ->count();

            if ($plazasOcupadas >= $practica->oferta->vacantes) {
                return response()->json(['error' => 'No puedes aceptar más candidatos. Has alcanzado el límite de vacantes (' . $practica->oferta->vacantes . ').'], 400);
            }
        }

        $practica->update(['estado' => $request->estado]);

        return response()->json(['message' => 'Estado actualizado a ' . $request->estado]);
    }

    /**
     * Valoración del Alumno hacia la Empresa.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id_practica
     * @return \Illuminate\Http\JsonResponse
     */
    public function valorarEmpresa(Request $request, $id_practica)
    {
        $request->validate([
            'puntuacion_empresa' => 'required|integer|min:1|max:5',
            'comentario_alumno' => 'nullable|string|max:500'
        ]);

        // Buscamos la práctica asegurándonos de que pertenece al alumno logueado
        $practica = Practica::where('id_alumno', Auth::user()->id_alumno)->findOrFail($id_practica);

        // Regla de Negocio 1: Solo prácticas terminadas
        if ($practica->estado !== 'FINALIZADA') {
            return response()->json(['error' => 'Solo puedes valorar unas prácticas que ya han finalizado.'], 403);
        }

        // Regla de Negocio 2: Solo se vota una vez
        if ($practica->puntuacion_empresa !== null) {
            return response()->json(['error' => 'Ya has valorado esta experiencia anteriormente.'], 400);
        }

        $practica->update([
            'puntuacion_empresa' => $request->puntuacion_empresa,
            'comentario_alumno' => $request->comentario_alumno
        ]);

        return response()->json(['message' => '¡Gracias por tu feedback! Valoración guardada con éxito.']);
    }
}
