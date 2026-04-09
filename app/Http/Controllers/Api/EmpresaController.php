<?php

/**
 * Controlador de API - EmpresaController
 * 
 * Gestiona el perfil privado de las Empresas.
 * Permite a la empresa visualizar la información con la que está registrada en la 
 * plataforma y actualizarla en de ser necesario.
 * 
 * @package App\Http\Controllers\Api
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaController extends Controller
{
    /**
     * Muestra el perfil de la empresa autenticada.
     * 
     * Retorna el modelo con los datos alojados en el token de Sanctum actual.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        return response()->json(Auth::user());
    }

    /**
     * Actualiza el perfil o datos de contacto.
     * 
     * Valida la presencia de datos básicos obligatorios, descarta aquello
     * que no se puede cambiar libremente (como el CIF si se aplica bloqueo) 
     * y guarda la configuración.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

        $user->update($request->only([
            'nombre_comercial', 
            'cif', 
            'telefono_contacto', 
            'direccion', 
            'ciudad', 
            'descripcion'
        ]));
        
        return response()->json(['message' => 'Perfil actualizado', 'user' => $user]);
    }
}
