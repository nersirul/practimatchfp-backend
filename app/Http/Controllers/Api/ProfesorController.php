<?php

/**
 * Controlador de API - ProfesorController
 * * Interfaz usada por los tutores de Formación Profesional para 
 * vigilar el progreso de las prácticas EN CURSO y asentar evaluacions (notas) finales,
 * permitiendo exportarlas formalmente como Reportes PDF.
 * * @package App\Http\Controllers\Api
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Practica;
use App\Models\Alumno;
use App\Models\Valoracion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ProfesorController extends Controller
{
    /**
     * Tablero de Control del Profesor (ACTUALIZADO SPRINT 7).
     * Localiza a todos los alumnos que este profesor tiene tutorizados, 
     * y extrae sus prácticas.
     * Incluye las que están ESPERANDO_TUTOR para generar la alerta en el frontend.
     * * @return \Illuminate\Http\JsonResponse
     */
    public function practicas()
    {
        $profesor = Auth::user();

        // Localización de IDs subyugados al tutor
        $misAlumnosIds = Alumno::where('id_profesor', $profesor->id_profesor)->pluck('id_alumno');

        // Hidratación de las prácticas correspondientes a la cohorte de alumnos
        $practicas = Practica::with(['alumno', 'oferta.empresa', 'valoracion'])
            ->whereIn('id_alumno', $misAlumnosIds)
            ->whereIn('estado', ['ESPERANDO_TUTOR', 'EN_CURSO', 'FINALIZADA']) // Incluimos el nuevo estado
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($practicas);
    }

    /**
     * Evaluar el ciclo y asentar calificación final.
     * * Recoge los metadatos de evaluación y comentarios cualitativos, los vincula 
     * a la tabla 'Valoracion', asume la autoría del acta (como supervisor oficial),
     * y da la práctica por clausurada al cambiar su estado.
     * * @param \Illuminate\Http\Request $request
     * @param int $id_practica ID principal de la candidatura / Match.
     * @return \Illuminate\Http\JsonResponse
     */
    public function evaluar(Request $request, $id_practica)
    {
        $request->validate([
            'calificacion' => 'required|in:APTO,NO APTO',
            'nota_numerica' => 'required|integer|min:1|max:10',
            'comentarios_profesor' => 'nullable|string'
        ]);

        $practica = Practica::findOrFail($id_practica);

        // Persistencia de la valoración cualitativa y cuantitativa en el registro histórico.
        Valoracion::create([
            'id_practica' => $id_practica,
            'calificacion' => $request->calificacion,
            'nota_numerica' => $request->nota_numerica,
            'comentarios_profesor' => $request->comentarios_profesor
        ]);

        $practica->update([
            'estado' => 'FINALIZADA',
            'id_profesor' => Auth::id()
        ]);

        return response()->json(['message' => 'Práctica evaluada y finalizada correctamente.']);
    }

    /**
     * Compilación y Generación en PDF.
     * * Utiliza la librería domPDF y los motores de parseado HTML renderizando
     * la plantilla Blade `informe_practica.blade.php`.
     * Retorna un archivo binario para forzar la descarga en el cliente.
     * * @param int $id_practica
     * @return \Illuminate\Http\Response
     */
    public function descargarPDF($id_practica)
    {
        $practica = Practica::with(['alumno', 'oferta.empresa', 'profesor', 'valoracion'])->findOrFail($id_practica);

        // Carga la información profunda hacia el renderizador estático
        $pdf = Pdf::loadView('pdf.informe_practica', compact('practica'));

        // Transmisión directa por streaming
        return $pdf->download('informe_fct_' . $practica->alumno->nombre . '.pdf');
    }

    /**
     * Búsqueda de alumnos sin tutor asignado dentro del centro del profesor.
     */
    public function alumnosSinTutor()
    {
        $profesor = Auth::user();
        $alumnos = Alumno::where('id_centro', $profesor->id_centro)
            ->whereNull('id_profesor')
            ->get();
        return response()->json($alumnos);
    }

    /**
     * Listado restrictivo de tutorizados.
     */
    public function misAlumnos()
    {
        $profesor = Auth::user();
        return response()->json(Alumno::where('id_profesor', $profesor->id_profesor)->get());
    }

    /**
     * Reclama o libera la tutoría de un alumno específico.
     */
    public function gestionarTutoria(Request $request, $id_alumno)
    {
        $profesor = Auth::user();
        $alumno = Alumno::findOrFail($id_alumno);

        if ($alumno->id_centro !== $profesor->id_centro) {
            return response()->json(['error' => 'Alumno de otro centro'], 403);
        }

        $accion = $request->accion; 
        $alumno->update(['id_profesor' => $accion === 'reclamar' ? $profesor->id_profesor : null]);

        return response()->json(['message' => 'Tutoría actualizada']);
    }

    /**
     * Aprobación del supervisor académico para convalidar el match y dar inicio a la práctica.
     */
    public function aprobarInicioPractica($id_practica)
    {
        $profesor = Auth::user();
        $practica = Practica::with('alumno')->findOrFail($id_practica);

        if ($practica->alumno->id_profesor !== $profesor->id_profesor) {
            return response()->json(['error' => 'No eres el tutor de este alumno'], 403);
        }

        $practica->update(['estado' => 'EN_CURSO', 'id_profesor' => $profesor->id_profesor]);
        return response()->json(['message' => 'Práctica iniciada oficialmente.']);
    }

    public function updatePerfil(Request $request)
    {
        $profesor = \App\Models\Profesor::findOrFail(Auth::user()->id_profesor);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'departamento' => 'required|string'
        ]);

        $profesor->update($request->only(['nombre', 'apellidos', 'telefono', 'departamento']));

        return response()->json(['message' => 'Perfil actualizado correctamente.']);
    }
}
