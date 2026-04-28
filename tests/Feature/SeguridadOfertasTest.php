<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\Empresa;
use App\Models\Oferta;
use App\Models\Administrador;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SeguridadOfertasTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de que una empresa no puede editar las ofertas de otra empresa
     */
    public function test_una_empresa_no_puede_editar_las_ofertas_de_otra_empresa()
    {
        $admin = Administrador::create(['nombre' => 'A', 'email' => 'a@a.com', 'password' => '123']);
        $empresaDueña = Empresa::create(['nombre_comercial' => 'Empresa 1', 'cif' => 'A11111111', 'email_contacto' => 'e1@e.com', 'password' => '123', 'sector' => 'IT', 'telefono_contacto' => '1', 'direccion' => 'D', 'ciudad' => 'C', 'activa' => true]);
        $empresaIntrusa = Empresa::create(['nombre_comercial' => 'Empresa 2', 'cif' => 'B22222222', 'email_contacto' => 'e2@e.com', 'password' => '123', 'sector' => 'IT', 'telefono_contacto' => '2', 'direccion' => 'D', 'ciudad' => 'C', 'activa' => true]);

        $oferta = Oferta::create(['id_empresa' => $empresaDueña->id_empresa, 'id_admin_validador' => $admin->id_admin, 'titulo' => 'Original', 'descripcion' => 'D', 'modalidad' => 'REMOTO', 'es_remunerada' => 1, 'posibilidad_contratacion' => 1, 'estado' => 'PUBLICADA', 'vacantes' => 1, 'activa' => true]);

        $payloadMaligno = ['titulo' => 'Hackeada', 'descripcion' => 'D', 'modalidad' => 'REMOTO'];
        
        $response = $this->actingAs($empresaIntrusa)->putJson("/api/ofertas/{$oferta->id_oferta}/editar", $payloadMaligno);

        $response->assertStatus(403);
        $this->assertDatabaseHas('ofertas', ['id_oferta' => $oferta->id_oferta, 'titulo' => 'Original']);
    }
}