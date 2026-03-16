<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Practica;
use App\Models\Oferta;
use Illuminate\Support\Facades\Auth;

class PracticaController extends Controller
{
    // 1. ALUMNO: Solicitar una práctica
    public function solicitar(Request $request, $id_oferta)
    {
        $user = Auth::user();
        if (!$user->id_alumno) return response()->json(['error' => 'Solo alumnos'], 403);

        // Comprobar si ya la ha solicitado
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
            // id_profesor se queda NULL hasta que se apruebe y se asigne
        ]);

        return response()->json(['message' => 'Solicitud enviada con éxito', 'practica' => $practica], 201);
    }

    // 2. ALUMNO: Ver mis candidaturas
    public function misCandidaturas()
    {
        $user = Auth::user();

        $practicas = Practica::with(['oferta.empresa'])
            ->where('id_alumno', $user->id_alumno)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($practicas);
    }

    // 3. EMPRESA: Ver candidatos de una oferta suya
    public function candidatosPorOferta($id_oferta)
    {
        $user = Auth::user();

        // Verificar que la oferta pertenece a esta empresa
        $oferta = Oferta::findOrFail($id_oferta);
        if ($oferta->id_empresa !== $user->id_empresa) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Obtener las solicitudes (prácticas) con los datos del alumno y sus tecnologías
        $candidaturas = Practica::with(['alumno.tecnologias'])
            ->where('id_oferta', $id_oferta)
            ->get();

        return response()->json($candidaturas);
    }

    // 4. EMPRESA: Cambiar estado del candidato (Aceptar/Rechazar)
    public function actualizarEstado(Request $request, $id_practica)
    {
        $user = Auth::user();

        $request->validate([
            'estado' => 'required|in:EN_CURSO,RECHAZADA,FINALIZADA'
        ]);

        $practica = Practica::with('oferta')->findOrFail($id_practica);

        // Verificar permisos
        if ($practica->oferta->id_empresa !== $user->id_empresa) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Si se acepta (EN_CURSO), opcionalmente se podría asignar el profesor aquí
        // Por ahora lo dejamos solo con el cambio de estado.
        $practica->update(['estado' => $request->estado]);

        return response()->json(['message' => 'Estado actualizado a ' . $request->estado]);
    }
}
