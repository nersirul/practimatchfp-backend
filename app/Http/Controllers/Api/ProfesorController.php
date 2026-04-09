<?php

/**
 * Controlador de API - ProfesorController
 * 
 * Interfaz usada por los tutores de Formación Profesional para 
 * vigilar el progreso de las prácticas EN CURSO y asentar evaluacions (notas) finales,
 * permitiendo exportarlas formalmente como Reportes PDF.
 * 
 * @package App\Http\Controllers\Api
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Practica;
use App\Models\Valoracion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ProfesorController extends Controller
{
    /**
     * Tablero de Control del Profesor.
     * 
     * Trae de la BD a todos los estudiantes que se encuentran activamente haciendo 
     * prácticas (EN_CURSO) o que las han concluido (FINALIZADA). 
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function practicasSupervisadas()
    {
        $practicas = Practica::with(['alumno', 'oferta.empresa', 'valoracion'])
            ->whereIn('estado', ['EN_CURSO', 'FINALIZADA'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($practicas);
    }

    /**
     * Evaluar el ciclo y asentar calificación final.
     * 
     * Recoge los metadatos de evaluación y comentarios cualitativos, los vincula 
     * a la tabla 'Valoracion', asume la autoría del acta (como supervisor oficial),
     * y da la práctica por clausurada al cambiar su estado.
     * 
     * @param \Illuminate\Http\Request $request
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
     * 
     * Utiliza la librería domPDF y los motores de parseado HTML renderizando
     * la plantilla Blade `informe_practica.blade.php`.
     * Retorna un archivo binario para forzar la descarga en el cliente.
     * 
     * @param int $id_practica
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
}
