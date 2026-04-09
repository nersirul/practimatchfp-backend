<?php

/**
 * Controlador de API - AuthController
 * 
 * Núcleo de autenticación. Maneja la emisión de Tokens (Sanctum),
 * los registros dinámicos según el perfil y el inicio de sesión.
 * 
 * @package App\Http\Controllers\Api
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Alumno;
use App\Models\Empresa;
use App\Models\Profesor;
use App\Models\Administrador;

class AuthController extends Controller
{
    /**
     * Endpoint de Autenticación (Login Múltiple).
     * 
     * Recibe correo electrónico, contraseña y un string identificando qué "tipo"
     * de perfil está intentando acceder. Realiza las validaciones de negocio
     * (por ejemplo, comprobar si una empresa está activada) y retorna el token.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException Si la autenticación falla.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'tipo' => 'required|in:alumno,empresa,admin,profesor',
        ]);

        $user = null;

        // Recuperar al modelo instanciado desde su propia tabla según el select del frontend
        switch ($request->tipo) {
            case 'alumno':
                $user = Alumno::where('email', $request->email)->first();
                break;
            case 'empresa':
                $user = Empresa::where('email_contacto', $request->email)->first();
                break;
            case 'admin':
                $user = Administrador::where('email', $request->email)->first();
                break;
            case 'profesor':
                $user = \App\Models\Profesor::where('email', $request->email)->first();
                break;
        }

        // Comprobar que realmente existía ese registro en la base de datos
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Las empresas requieren un chequeo de seguridad extra
        if ($request->tipo === 'empresa' && $user->activa == false) {
            throw ValidationException::withMessages([
                'email' => ['Tu cuenta de empresa está pendiente de validación por el centro educativo.'],
            ]);
        }

        // Validar que el Hash de la BD concuerde con la pass en texto plano
        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Generar un token único asilando el Rol en su metadata de Sanctum
        $token = $user->createToken($request->tipo . '-token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'token' => $token,
            'tipo' => $request->tipo,
            'user' => $user
        ]);
    }

    /**
     * Registro rápido exclusivo para alumnos (Legacy / Alternatif).
     * 
     * Usado internamente o en flujos reducidos si fuera necesario.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerAlumno(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'apellidos' => 'required',
            'nif' => 'required|unique:alumnos',
            'email' => 'required|email|unique:alumnos',
            'password' => 'required|min:6',
        ]);

        $alumno = Alumno::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'nif' => $request->nif,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'modalidad_preferida' => 'PRESENCIAL' // Asiganación por defecto
        ]);

        return response()->json([
            'message' => 'Alumno registrado correctamente',
            'user' => $alumno
        ], 201);
    }

    /**
     * Cierre limpio de sesión.
     * 
     * Invalida (borrando de la BD de Sanctum) el token exacto con el que 
     * el usuario hizo la petición actual, sin desconectarlo de otros dispositivos.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }

    /**
     * Endpoint de Registro Unificado inteligente.
     * 
     * Valida según reglas distintas la creación de alumno, empresa o profesor en 
     * base a lo emitido por el formulario único de react.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registro(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:alumno,empresa,profesor',
            'password' => 'required|min:6'
        ]);

        if ($request->tipo === 'alumno') {
            $request->validate([
                'nif' => 'required|string|unique:alumnos,nif',
                'nombre' => 'required|string|max:255',
                'apellidos' => 'required|string|max:255',
                'email' => 'required|email|unique:alumnos,email',
                'ciclo' => 'required|string',
                'telefono' => 'nullable|string',
                'direccion' => 'nullable|string',
                'ciudad' => 'nullable|string',
            ]);

            Alumno::create([
                'nif' => $request->nif,
                'nombre' => $request->nombre,
                'apellidos' => $request->apellidos,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'ciclo' => $request->ciclo,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'ciudad' => $request->ciudad,
            ]);
        } elseif ($request->tipo === 'empresa') {
            $request->validate([
                'cif' => 'required|string|unique:empresas,cif',
                'nombre_comercial' => 'required|string|max:255',
                'email' => 'required|email|unique:empresas,email_contacto',
                'telefono_contacto' => 'required|string',
                'direccion' => 'required|string',
                'ciudad' => 'required|string',
                'descripcion' => 'nullable|string',
            ]);

            Empresa::create([
                'cif' => $request->cif,
                'nombre_comercial' => $request->nombre_comercial,
                'email_contacto' => $request->email,
                'password' => Hash::make($request->password),
                'telefono_contacto' => $request->telefono_contacto,
                'direccion' => $request->direccion,
                'ciudad' => $request->ciudad,
                'descripcion' => $request->descripcion,
            ]);
        } elseif ($request->tipo === 'profesor') {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'apellidos' => 'required|string|max:255',
                'email' => 'required|email|unique:profesores,email',
                'departamento' => 'required|string',
                'telefono' => 'nullable|string',
            ]);

            Profesor::create([
                'nombre' => $request->nombre,
                'apellidos' => $request->apellidos,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'departamento' => $request->departamento,
                'telefono' => $request->telefono,
            ]);
        }

        return response()->json(['message' => 'Usuario registrado con éxito.'], 201);
    }
}
