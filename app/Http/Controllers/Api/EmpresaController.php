<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaController extends Controller
{
    public function show()
    {
        return response()->json(Auth::user());
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'nombre_comercial' => 'required',
            'cif' => 'required',
            'telefono_contacto' => 'required',
            'direccion' => 'required',
            'ciudad' => 'required',
        ]);

        $user->update($request->only(['nombre_comercial', 'cif', 'telefono_contacto', 'direccion', 'ciudad', 'descripcion']));
        return response()->json(['message' => 'Perfil actualizado', 'user' => $user]);
    }
}
