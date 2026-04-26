<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ValidacionAlumnoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_nif_del_alumno_debe_tener_un_formato_valido()
    {
        // 1. DATO INVÁLIDO (Completamos todos los campos para que no salte error por campos vacíos)
        $payloadInvalido = [
            'nombre' => 'Juan',
            'apellidos' => 'Pérez',
            'nif' => '123Z', // <- Este es el error que queremos cazar (demasiado corto)
            'email' => 'test@test.com',
            'password' => 'password123',
            'ciclo' => 'DAW',
            'modalidad_preferida' => 'REMOTO',
            'nombre_centro' => 'IES Falso'
        ];
        
        $response = $this->postJson('/api/register/alumno', $payloadInvalido);
        $response->assertStatus(422)->assertJsonValidationErrors(['nif']);

        // 2. DATO VÁLIDO
        $payloadValido = $payloadInvalido;
        $payloadValido['nif'] = '12345678Z'; // Formato correcto
        $payloadValido['email'] = 'test2@test.com'; // Único
        
        $response2 = $this->postJson('/api/register/alumno', $payloadValido);
        $response2->assertStatus(201); // o 201
    }
}