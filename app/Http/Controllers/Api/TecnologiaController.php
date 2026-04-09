<?php

/**
 * Controlador de API - TecnologiaController
 * 
 * Controlador base que gestiona el CRUD del catálogo de Tecnologías.
 * 
 * @package App\Http\Controllers\Api
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tecnologia;

class TecnologiaController extends Controller
{
    /**
     * Listado general (All)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return Tecnologia::all();
    }

    /**
     * Crea un diccionario de tecnología nuevo.
     * 
     * Evalua unicidad de nombre antes de inyectar a BbbD para evitar duplicados como "ReactJS" / "React".
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate(['nombre' => 'required|unique:tecnologias,nombre']);
        $tecnologia = Tecnologia::create($request->all());
        return response()->json($tecnologia, 201);
    }

    /**
     * Actualiza un nombre de tecnología si se escribió con faltas ortográficas.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $tecnologia = Tecnologia::findOrFail($id);
        $request->validate(['nombre' => 'required|unique:tecnologias,nombre,' . $id . ',id_tecnologia']);
        $tecnologia->update($request->all());
        return response()->json($tecnologia);
    }

    /**
     * Eliminación permanente.
     * 
     * Eliminará en cascada las relaciones con Ofertas y Alumnos.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        Tecnologia::destroy($id);
        return response()->json(['message' => 'Eliminada']);
    }
}
