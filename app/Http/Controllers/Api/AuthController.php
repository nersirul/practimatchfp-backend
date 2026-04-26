<?php

/**
 * Controlador de API - AuthController
 * * Núcleo de autenticación. Maneja la emisión de Tokens (Sanctum),
 * los registros dinámicos según el perfil y el inicio de sesión.
 * * @package App\Http\Controllers\Api
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
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'tipo' => 'required|in:alumno,empresa,admin,profesor',
        ]);

        $user = null;

        // Identificación y recuperación de la entidad orígen según el discriminador de perfil
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

        // Validación de existencia en el origen de datos
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Control de Admisión (Exclusivo Sector Privado): Requisito de validación institucional
        if ($request->tipo === 'empresa' && $user->activa == false) {
            throw ValidationException::withMessages([
                'email' => ['Tu cuenta de empresa está pendiente de validación por el centro educativo.'],
            ]);
        }

        // Verificación de credenciales criptográficas
        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Emisión del JWT/Bearer a través de Sanctum aislando capacidades por rol
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
     */
    public function registerAlumno(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'apellidos' => 'required',
            // NUEVO: Regla Regex estricta (8 números y 1 letra)
            'nif' => ['required', 'unique:alumnos', 'regex:/^[0-9]{8}[A-Za-z]$/'],
            'email' => 'required|email|unique:alumnos',
            'password' => 'required|min:6',
            'nombre_centro' => 'required|string',
        ]);

        $centro = \App\Models\Centro::firstOrCreate(['nombre' => $request->nombre_centro]);

        $alumno = Alumno::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'nif' => $request->nif,
            'id_centro' => $centro->id_centro,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'modalidad_preferida' => 'PRESENCIAL' // Asignación por defecto
        ]);

        return response()->json([
            'message' => 'Alumno registrado correctamente',
            'user' => $alumno
        ], 201);
    }

    /**
     * Cierre limpio de sesión.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }

    /**
     * Endpoint de Registro Unificado inteligente.
     */
    public function registro(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:alumno,empresa,profesor',
            'password' => 'required|min:6'
        ]);

        if ($request->tipo === 'alumno') {
            $request->validate([
                'nombre_centro' => 'required|string',
                // NUEVO: Regla Regex estricta (8 números y 1 letra)
                'nif' => ['required', 'string', 'unique:alumnos,nif', 'regex:/^[0-9]{8}[A-Za-z]$/'],
                'nombre' => 'required|string|max:255',
                'apellidos' => 'required|string|max:255',
                'email' => 'required|email|unique:alumnos,email',
                'ciclo' => 'required|string',
                'telefono' => 'nullable|string',
                'direccion' => 'nullable|string',
                'ciudad' => 'nullable|string',
            ]);

            $centro = \App\Models\Centro::firstOrCreate(['nombre' => $request->nombre_centro]);

            Alumno::create([
                'id_centro' => $centro->id_centro,
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
                // NUEVO: Regla Regex estricta (1 letra y 8 números)
                'cif' => ['required', 'string', 'unique:empresas,cif', 'regex:/^[A-Za-z][0-9]{8}$/'],
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
                'nombre_centro' => 'required|string',
                'nombre' => 'required|string|max:255',
                'apellidos' => 'required|string|max:255',
                'email' => 'required|email|unique:profesores,email',
                'departamento' => 'required|string',
                'telefono' => 'nullable|string',
            ]);

            $centro = \App\Models\Centro::firstOrCreate(['nombre' => $request->nombre_centro]);

            Profesor::create([
                'id_centro' => $centro->id_centro,
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