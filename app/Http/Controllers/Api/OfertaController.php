<?php

/**
 * Controlador de API - OfertaController
 * 
 * Orquesta casi todo el ciclo de vida de la clase Oferta (creación, validación, publicación).
 * Implementa métodos consumidos asimétricamente por Alumnos, Empresas y Administradores.
 * 
 * @package App\Http\Controllers\Api
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Oferta;
use Illuminate\Support\Facades\Auth;

class OfertaController extends Controller
{
    /**
     * Buscador de Ofertas para Alumnos.
     * 
     * Retorna mediante paginación las vacantes que están "PUBLICADAS", 
     * permitiendo usar filtros opcionales de 'modalidad' o array de 'tecnologias'
     * procesados a través del Scope Local del Modelo Oferta.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filtros = $request->only(['modalidad', 'tecnologias']);

        $ofertas = Oferta::with(['empresa:id_empresa,nombre_comercial,ciudad', 'tecnologias'])
            ->filtros($filtros)
            ->orderBy('created_at', 'desc')
            ->paginate(10); 

        return response()->json($ofertas);
    }

    /**
     * Panel privado de Ofertas propias (Empresas).
     * 
     * Devuelve a la empresa un listado de *todas* las ofertas que ha creado ella misma,
     * inyectando un contador 'practicas_count' para saber el volumen de solicitantes que tiene cada una.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function misOfertas()
    {
        $user = Auth::user();
        if (!$user->id_empresa) return response()->json(['error' => 'No autorizado'], 403);

        $ofertas = Oferta::with('tecnologias')
            ->withCount('practicas') 
            ->where('id_empresa', $user->id_empresa)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($ofertas);
    }

    /**
     * Publicar o registrar una nueva Oferta (Empresas).
     * 
     * Instancia un requerimiento de Prácticas para que el colegio lo valide.
     * Por defecto arranca en estado "PENDIENTE".
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->id_empresa) return response()->json(['error' => 'No autorizado'], 403);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'modalidad' => 'required|in:REMOTO,PRESENCIAL,HIBRIDO',
            'tecnologias' => 'array' 
        ]);

        $oferta = Oferta::create([
            'id_empresa' => $user->id_empresa,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'modalidad' => $request->modalidad,
            'es_remunerada' => $request->es_remunerada ?? false,
            'posibilidad_contratacion' => $request->posibilidad_contratacion ?? false,
            'estado' => 'PENDIENTE'
        ]);

        // Vincula los requerimientos técnicos vía tabla intermedia
        if ($request->has('tecnologias')) {
            $oferta->tecnologias()->attach($request->tecnologias);
        }

        return response()->json(['message' => 'Oferta enviada a validación', 'oferta' => $oferta], 201);
    }

    /**
     * Bandeja de Revisión para Administradores.
     * 
     * Muestra a los coordinadores del FCT las ofertas de las empresas que acaban de llegar
     * y están esperando sanción ("PENDIENTE").
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function pendientes()
    {
        $ofertas = Oferta::with(['empresa:id_empresa,nombre_comercial', 'tecnologias'])
            ->where('estado', 'PENDIENTE')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($ofertas);
    }

    /**
     * Resolución de Adminitrador (Validar/Rechazar).
     * 
     * Toma una decisión vinculante sobre la publicación de la Oferta y archiva la
     * firma del administrador que lo autorizó.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id ID de la oferta bajo inspección.
     * @return \Illuminate\Http\JsonResponse
     */
    public function validar(Request $request, $id)
    {
        $admin = Auth::user();
        if (!$admin->id_admin) return response()->json(['error' => 'No autorizado'], 403);

        $request->validate([
            'estado' => 'required|in:PUBLICADA,CERRADA' 
        ]);

        $oferta = Oferta::findOrFail($id);

        $oferta->update([
            'estado' => $request->estado,
            'id_admin_validador' => $admin->id_admin 
        ]);

        return response()->json(['message' => 'Oferta actualizada a ' . $request->estado]);
    }

    /**
     * Endpoint de Ficha Pública de la Oferta (Alumnos).
     * 
     * Despliega en pantalla grande los detalles plenos de la vacante seleccionada,
     * asegurándose que la url no pueda espiar ofertas privadas (forzando validación de estado=PUBLICADA).
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $oferta = Oferta::with(['empresa:id_empresa,nombre_comercial,ciudad,descripcion', 'tecnologias'])
            ->where('estado', 'PUBLICADA')
            ->findOrFail($id);
            
        return response()->json($oferta);
    }
}
