<?php

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
    // LOGIN GENERAL (Recibe email, password y tipo)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'tipo' => 'required|in:alumno,empresa,admin,profesor',
        ]);

        $user = null;

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
            case 'profesor':
                $user = \App\Models\Profesor::where('email', $request->email)->first();
                break;
        }

        // 1. Verificamos que el usuario existe en la base de datos
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // 2. Si es empresa, existe, pero no está activa, bloqueamos el acceso
        if ($request->tipo === 'empresa' && $user->activa == false) {
            throw ValidationException::withMessages([
                'email' => ['Tu cuenta de empresa está pendiente de validación por el centro educativo.'],
            ]);
        }

        // 3. Verificamos que la contraseña es correcta
        if (! Hash::check($request->password, $user->password)) {
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
                'telefono_contacto' => 'required|string', // Obligatorio para empresas
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
