<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlumnoController extends Controller
{
    public function show()
    {
        // Devuelve el usuario y sus tecnologías relacionadas
        return response()->json(Auth::user()->load('tecnologias'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. Actualizar Datos Básicos
        $request->validate([
            'nombre' => 'required',
            'apellidos' => 'required',
            'ciclo' => 'required',
            'modalidad_preferida' => 'required',
        ]);

        $user->update($request->only(['nombre', 'apellidos', 'ciclo', 'modalidad_preferida']));

        // 2. Sincronizar Tecnologías (Relación N:M)
        if ($request->has('tecnologias')) {
            $syncData = [];
            foreach ($request->input('tecnologias') as $tec) {
                $syncData[$tec['id_tecnologia']] = [
                    'nivel' => $tec['nivel'] ?? 1,
                    'tipo_relacion' => $tec['tipo_relacion'] ?? 'INTERES'
                ];
            }
            $user->tecnologias()->sync($syncData);
        }

        return response()->json(['message' => 'Perfil actualizado', 'user' => $user->load('tecnologias')]);
    }
}
