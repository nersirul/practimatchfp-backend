<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Alumno;
use App\Models\Empresa;
use App\Models\Administrador;

class AuthController extends Controller
{
    // LOGIN GENERAL (Recibe email, password y tipo)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'tipo' => 'required|in:alumno,empresa,admin'
        ]);

        $user = null;
        $model = null;

        // Seleccionamos el modelo según el tipo
        switch ($request->tipo) {
            case 'alumno':
                $user = Alumno::where('email', $request->email)->first();
                break;
            case 'empresa':
                $user = Empresa::where('email_contacto', $request->email)->first(); // Ojo, el campo es email_contacto
                break;
            case 'admin':
                $user = Administrador::where('email', $request->email)->first();
                break;
        }

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Creamos el token (Sanctum)
        // El nombre del token incluye el rol para identificarlo luego si hace falta
        $token = $user->createToken($request->tipo . '-token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'token' => $token,
            'tipo' => $request->tipo,
            'user' => $user
        ]);
    }

    // REGISTRO ALUMNO
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
            'modalidad_preferida' => 'PRESENCIAL' // Valor por defecto
        ]);

        return response()->json([
            'message' => 'Alumno registrado correctamente',
            'user' => $alumno
        ], 201);
    }

    public function logout(Request $request)
    {
        // Borra el token actual
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }
}
