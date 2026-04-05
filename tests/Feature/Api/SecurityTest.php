<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Alumno;
use Illuminate\Support\Facades\Hash;

class SecurityTest extends TestCase
{
    /**
     * El trait RefreshDatabase es VITAL.
     * Le dice a Laravel: "Antes de cada test, crea todas las tablas de cero. 
     * Cuando termine el test, bórralas". Así los tests no interfieren entre sí.
     */
    use RefreshDatabase;

    /**
     * TEST 1: Verificar que el login rechaza contraseñas falsas.
     */
    public function test_usuario_no_puede_loguearse_con_contrasena_incorrecta()
    {
        // 1. Arrange (Preparación): Creamos un alumno temporal en la BD de pruebas en memoria
        $alumno = Alumno::create([
            'nif' => '12345678A',
            'nombre' => 'Test',
            'apellidos' => 'User',
            'email' => 'test@alumno.com',
            'password' => Hash::make('password_correcta'), // Esta es la buena
            'ciclo' => 'DAW'
        ]);

        // 2. Act (Acción): Simulamos una petición POST de un hacker / usuario despistado
        $response = $this->postJson('/api/login', [
            'email' => 'test@alumno.com',
            'password' => 'clave_falsa_inventada', // Esta es la mala
            'tipo' => 'alumno'
        ]);

        // 3. Assert (Afirmación): Comprobamos que el sistema devuelve un Error 422 de Validación
        // y que el error especifica que falla el campo 'email' (por nuestro throw ValidationException)
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * TEST 2: Verificar que las rutas protegidas requieren Token Sanctum.
     */
    public function test_usuario_anonimo_es_rechazado_en_rutas_protegidas()
    {
        // 1. Act (Acción): Simulamos que alguien intenta ver las candidaturas por la URL
        // sin haber iniciado sesión (sin enviar un Token en las cabeceras).
        $response = $this->getJson('/api/alumno/candidaturas');

        // 2. Assert (Afirmación): El sistema (Middleware auth:sanctum) debe interceptarlo
        // y devolver un Error 401 (Unauthenticated).
        $response->assertStatus(401);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}
