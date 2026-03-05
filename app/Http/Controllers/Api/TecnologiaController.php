<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tecnologia;

class TecnologiaController extends Controller
{
    public function index()
    {
        return Tecnologia::all();
    }

    public function store(Request $request)
    {
        $request->validate(['nombre' => 'required|unique:tecnologias,nombre']);
        $tecnologia = Tecnologia::create($request->all());
        return response()->json($tecnologia, 201);
    }

    public function update(Request $request, $id)
    {
        $tecnologia = Tecnologia::findOrFail($id);
        $request->validate(['nombre' => 'required|unique:tecnologias,nombre,' . $id . ',id_tecnologia']);
        $tecnologia->update($request->all());
        return response()->json($tecnologia);
    }

    public function destroy($id)
    {
        Tecnologia::destroy($id);
        return response()->json(['message' => 'Eliminada']);
    }
}
