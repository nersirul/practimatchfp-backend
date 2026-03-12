<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Oferta;
use Illuminate\Support\Facades\Auth;

class OfertaController extends Controller
{
    // 1. ALUMNO: Buscador público de ofertas (Usa el Scope que creamos)
    public function index(Request $request)
    {
        // Obtenemos los filtros por querystring (?modalidad=REMOTO&tecnologias[]=1)
        $filtros = $request->only(['modalidad', 'tecnologias']);

        $ofertas = Oferta::with(['empresa:id_empresa,nombre_comercial,ciudad', 'tecnologias'])
            ->filtros($filtros)
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Paginación para no saturar la vista

        return response()->json($ofertas);
    }

    // 2. EMPRESA: Ver solo mis ofertas
    public function misOfertas()
    {
        $user = Auth::user();
        if (!$user->id_empresa) return response()->json(['error' => 'No autorizado'], 403);

        $ofertas = Oferta::with('tecnologias')
            ->where('id_empresa', $user->id_empresa)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($ofertas);
    }

    // 3. EMPRESA: Crear nueva oferta
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->id_empresa) return response()->json(['error' => 'No autorizado'], 403);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'modalidad' => 'required|in:REMOTO,PRESENCIAL,HIBRIDO',
            'tecnologias' => 'array' // Array de IDs de tecnologías requeridas
        ]);

        // La oferta nace PENDIENTE por defecto
        $oferta = Oferta::create([
            'id_empresa' => $user->id_empresa,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'modalidad' => $request->modalidad,
            'es_remunerada' => $request->es_remunerada ?? false,
            'posibilidad_contratacion' => $request->posibilidad_contratacion ?? false,
            'estado' => 'PENDIENTE'
        ]);

        // Relacionamos las tecnologías en la tabla pivote
        if ($request->has('tecnologias')) {
            $oferta->tecnologias()->attach($request->tecnologias);
        }

        return response()->json(['message' => 'Oferta enviada a validación', 'oferta' => $oferta], 201);
    }

    // 4. ADMIN: Ver ofertas pendientes de validación
    public function pendientes()
    {
        // Solo admins deberían llegar aquí (lo protegeremos en las rutas)
        $ofertas = Oferta::with(['empresa:id_empresa,nombre_comercial', 'tecnologias'])
            ->where('estado', 'PENDIENTE')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($ofertas);
    }

    // 5. ADMIN: Validar oferta (Aprobar o Rechazar)
    public function validar(Request $request, $id)
    {
        $admin = Auth::user();
        if (!$admin->id_admin) return response()->json(['error' => 'No autorizado'], 403);

        $request->validate([
            'estado' => 'required|in:PUBLICADA,CERRADA' // CERRADA funciona como rechazada en nuestro enum
        ]);

        $oferta = Oferta::findOrFail($id);

        $oferta->update([
            'estado' => $request->estado,
            'id_admin_validador' => $admin->id_admin // Registramos quién la aprobó
        ]);

        return response()->json(['message' => 'Oferta actualizada a ' . $request->estado]);
    }
}
