<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Practica;
use App\Models\Valoracion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ProfesorController extends Controller
{
    // 1. Ver prácticas activas o finalizadas para supervisar
    public function practicasSupervisadas()
    {
        // Para el MVP, la profesora ve todas las prácticas en curso o finalizadas.
        $practicas = Practica::with(['alumno', 'oferta.empresa', 'valoracion'])
            ->whereIn('estado', ['EN_CURSO', 'FINALIZADA'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($practicas);
    }

    // 2. Evaluar y finalizar práctica
    public function evaluar(Request $request, $id_practica)
    {
        $request->validate([
            'calificacion' => 'required|in:APTO,NO APTO',
            'nota_numerica' => 'required|integer|min:1|max:10',
            'comentarios_profesor' => 'nullable|string'
        ]);

        $practica = Practica::findOrFail($id_practica);

        // Crear la valoración
        Valoracion::create([
            'id_practica' => $id_practica,
            'calificacion' => $request->calificacion,
            'nota_numerica' => $request->nota_numerica,
            'comentarios_profesor' => $request->comentarios_profesor
        ]);

        // Cambiar estado a FINALIZADA y asignar a este profesor como supervisor oficial
        $practica->update([
            'estado' => 'FINALIZADA',
            'id_profesor' => Auth::id()
        ]);

        return response()->json(['message' => 'Práctica evaluada y finalizada correctamente.']);
    }

    // 3. Generar PDF
    public function descargarPDF($id_practica)
    {
        $practica = Practica::with(['alumno', 'oferta.empresa', 'profesor', 'valoracion'])->findOrFail($id_practica);

        // Cargamos una vista HTML y la pasamos a PDF
        $pdf = Pdf::loadView('pdf.informe_practica', compact('practica'));

        // Retornamos el archivo para forzar su descarga
        return $pdf->download('informe_fct_' . $practica->alumno->nombre . '.pdf');
    }
}
