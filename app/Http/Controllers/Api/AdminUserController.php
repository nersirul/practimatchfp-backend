<?php

/**
 * Controlador de API - AdminUserController
 * 
 * Gestiona el panel de control del SuperAdministrador.
 * Proporciona métodos para verificar, validar y borrar usuarios (SoftDelete) del sistema.
 * 
 * @package App\Http\Controllers\Api
 */

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
    /**
     * Obtener listado de empresas no validadas.
     * 
     * Retorna todas las empresas registradas que todavía tienen el campo activa=false.
     * 
     * @return \Illuminate\Http\JsonResponse JSON con el array de empresas.
     */
    public function empresasPendientes()
    {
        $empresas = Empresa::where('activa', false)->get();
        return response()->json($empresas);
    }

    /**
     * Validar Empresa.
     * 
     * Activa una empresa cambiándole el estado en la base de datos a true.
     * Esto le permitirá loguearse y que sus ofertas empiecen a indexarse.
     * 
     * @param int $id ID de la empresa a validar.
     * @return \Illuminate\Http\JsonResponse
     */
    public function validarEmpresa($id)
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->update(['activa' => true]);
        return response()->json(['message' => 'Empresa validada y activada con éxito.']);
    }

    /**
     * Listado dinámico de usuarios según su rol.
     * 
     * @param string $tipo Tipo de usuario ('alumnos', 'empresas', 'profesores', 'administradores').
     * @return \Illuminate\Http\JsonResponse JSON con la colección de usuarios correspondientes.
     */
    public function index($tipo)
    {
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

    /**
     * Actualizar datos o contraseña de cualquier usuario del sistema.
     *
     * Permite al administrador editar la información desde el panel de control.
     * Encripta automáticamente la nueva contraseña si se provee.
     * 
     * @param \Illuminate\Http\Request $request
     * @param string $tipo El perfil o rol del usuario
     * @param int $id ID del usuario a modificar
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $tipo, $id)
    {
        $modelo = $this->getModelInstance($tipo, $id);
        if (!$modelo) return response()->json(['error' => 'Usuario no encontrado'], 404);

        // Cogemos todos los campos modificados salvo la contraseña para tratarla aparte.
        $datos = $request->except(['password']); 

        // Si el payload contiene una contraseña, la preparamos hasheándola.
        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $modelo->update($datos);
        return response()->json(['message' => 'Usuario actualizado correctamente']);
    }

    /**
     * Borrado lógico de un usuario (Soft Delete).
     * 
     * Permuta el registro a estado "eliminado" sin borrar de la base de datos realmente,
     * útil para no romper referencias de FK en prácticas ya asociadas.
     * Previene que un administrador se de de baja a sí mismo y quede el sistema huérfano.
     * 
     * @param string $tipo El rol especificado del usuario.
     * @param int $id ID del usuario atado al rol.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($tipo, $id)
    {
        // Medida de seguridad: el superadmin activo no puede autodestruirse.
        if ($tipo === 'administradores' && $id == Auth::user()->id_admin) {
            return response()->json(['error' => 'Operación denegada. No puedes darte de baja a ti mismo.'], 403);
        }

        $modelo = $this->getModelInstance($tipo, $id);
        if (!$modelo) return response()->json(['error' => 'Usuario no encontrado'], 404);

        // Ejecuta SoftDelete según Trait del modelo.
        $modelo->delete(); 
        
        return response()->json(['message' => 'Usuario dado de baja correctamente']);
    }

    /**
     * Factory Method (Función auxiliar).
     * 
     * Devuelve una instancia concreta del modelo Eloquent según el string provisto, o nulo.
     * 
     * @param string $tipo Selector de tipo de usuario.
     * @param int $id Identificador primario.
     * @return \Illuminate\Database\Eloquent\Model|null
     */
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
