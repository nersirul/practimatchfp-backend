<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Alumno;
use App\Models\Empresa;
use App\Models\Profesor;
use App\Models\Administrador;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    // 1. VALIDACIÓN DE EMPRESAS
    public function empresasPendientes()
    {
        $empresas = Empresa::where('activa', false)->get();
        return response()->json($empresas);
    }

    public function validarEmpresa($id)
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->update(['activa' => true]);
        return response()->json(['message' => 'Empresa validada y activada con éxito.']);
    }

    // 2. LISTADO DINÁMICO DE USUARIOS
    public function index($tipo)
    {
        // El administrador pide ver una lista (alumnos, empresas, profesores)
        switch ($tipo) {
            case 'alumnos':
                return response()->json(Alumno::all());
            case 'empresas':
                return response()->json(Empresa::all());
            case 'profesores':
                return response()->json(Profesor::all());
            case 'administradores':
                return response()->json(Administrador::all());
            default:
                return response()->json(['error' => 'Tipo inválido'], 400);
        }
    }

    // 3. ACTUALIZAR DATOS / CONTRASEÑA
    public function update(Request $request, $tipo, $id)
    {
        $modelo = $this->getModelInstance($tipo, $id);
        if (!$modelo) return response()->json(['error' => 'Usuario no encontrado'], 404);

        $datos = $request->except(['password']); // Cogemos todo menos la pass

        // Si el admin envía una contraseña nueva, la encriptamos
        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $modelo->update($datos);
        return response()->json(['message' => 'Usuario actualizado correctamente']);
    }

    // 4. BORRADO LÓGICO (Soft Delete)
    public function destroy($tipo, $id)
    {
        // PROTECCIÓN: El admin no puede borrarse a sí mismo
        if ($tipo === 'administradores' && $id == Auth::user()->id_admin) {
            return response()->json(['error' => 'Operación denegada. No puedes darte de baja a ti mismo.'], 403);
        }

        $modelo = $this->getModelInstance($tipo, $id);
        if (!$modelo) return response()->json(['error' => 'Usuario no encontrado'], 404);

        $modelo->delete(); // Esto hace el SoftDelete automático
        return response()->json(['message' => 'Usuario dado de baja correctamente']);
    }

    // Función auxiliar para no repetir código
    private function getModelInstance($tipo, $id)
    {
        switch ($tipo) {
            case 'alumnos':
                return Alumno::find($id);
            case 'empresas':
                return Empresa::find($id);
            case 'profesores':
                return Profesor::find($id);
            case 'administradores':
                return Administrador::find($id);
            default:
                return null;
        }
    }
}
