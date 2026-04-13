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

        // 1. Buscamos los IDs de TODOS los alumnos que te pertenecen
        $misAlumnosIds = Alumno::where('id_profesor', $profesor->id_profesor)->pluck('id_alumno');

        // 2. Buscamos las prácticas de esos alumnos y cargamos las relaciones para React
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

        // Crear la valoración cualitativa en base de datos.
        Valoracion::create([
            'id_practica' => $id_practica,
            'calificacion' => $request->calificacion,
            'nota_numerica' => $request->nota_numerica,
            'comentarios_profesor' => $request->comentarios_profesor
        ]);

        // Cambiar estado a FINALIZADA y grabar a fuego la huella del profesor.
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

    // Listar alumnos de su centro que no tienen tutor asignado
    public function alumnosSinTutor()
    {
        $profesor = Auth::user();
        $alumnos = Alumno::where('id_centro', $profesor->id_centro)
            ->whereNull('id_profesor')
            ->get();
        return response()->json($alumnos);
    }

    // Listar sus propios alumnos
    public function misAlumnos()
    {
        $profesor = Auth::user();
        return response()->json(Alumno::where('id_profesor', $profesor->id_profesor)->get());
    }

    // Asignar o desasignar alumno
    public function gestionarTutoria(Request $request, $id_alumno)
    {
        $profesor = Auth::user();
        $alumno = Alumno::findOrFail($id_alumno);

        if ($alumno->id_centro !== $profesor->id_centro) {
            return response()->json(['error' => 'Alumno de otro centro'], 403);
        }

        $accion = $request->accion; // 'reclamar' o 'soltar'
        $alumno->update(['id_profesor' => $accion === 'reclamar' ? $profesor->id_profesor : null]);

        return response()->json(['message' => 'Tutoría actualizada']);
    }

    // El paso final de seguridad: Profesor aprueba el inicio de prácticas
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
}
