<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ValidacionEmpresaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de que el cif y el email de la empresa deben cumplir las reglas
     */
    public function test_el_cif_y_email_de_empresa_deben_cumplir_las_reglas()
    {
        $payloadInvalido = [
            'tipo' => 'empresa',
            'nombre_comercial' => 'Tech Corp',
            'cif' => '123', // Inválido
            'email' => 'correo-sin-arroba', // Inválido
            'password' => 'password',
            'sector' => 'IT',
            'telefono_contacto' => '123456789',
            'direccion' => 'Calle Falsa 123',
            'ciudad' => 'Madrid'
        ];
        
        $response = $this->postJson('/api/registro', $payloadInvalido);
        $response->assertStatus(422)->assertJsonValidationErrors(['cif', 'email']);

        $payloadValido = $payloadInvalido;
        $payloadValido['cif'] = 'B12345678';
        $payloadValido['email'] = 'contacto@techcorp.com';
        
        $response2 = $this->postJson('/api/registro', $payloadValido);
        $response2->assertSuccessful();
    }
}