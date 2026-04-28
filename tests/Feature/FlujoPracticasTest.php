<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\Alumno;
use App\Models\Oferta;
use App\Models\Empresa;
use App\Models\Centro;
use App\Models\Administrador;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FlujoPracticasTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de un alumno puede solicitar una oferta activa
     */
    public function test_un_alumno_puede_solicitar_una_oferta_activa()
    {
        $admin = Administrador::create(['nombre' => 'Admin', 'email' => 'admin@test.com', 'password' => '123']);
        $centro = Centro::create(['nombre' => 'IES Test']);
        $empresa = Empresa::create(['nombre_comercial' => 'Tech', 'cif' => 'B12345678', 'email_contacto' => 't@t.com', 'password' => '123', 'sector' => 'IT', 'telefono_contacto' => '1', 'direccion' => 'C', 'ciudad' => 'M', 'activa' => true]);
        $oferta = Oferta::create(['id_empresa' => $empresa->id_empresa, 'id_admin_validador' => $admin->id_admin, 'titulo' => 'Test', 'descripcion' => 'D', 'modalidad' => 'REMOTO', 'es_remunerada' => true, 'posibilidad_contratacion' => true, 'estado' => 'PUBLICADA', 'vacantes' => 2, 'activa' => true]);
        $alumno = Alumno::create(['id_centro' => $centro->id_centro, 'nombre' => 'A', 'apellidos' => 'B', 'nif' => '12345678Z', 'email' => 'a@a.com', 'password' => '123', 'ciclo' => 'DAW', 'modalidad_preferida' => 'REMOTO', 'telefono' => '1', 'direccion' => 'D', 'ciudad' => 'M']);

        $response = $this->actingAs($alumno)->postJson("/api/ofertas/{$oferta->id_oferta}/solicitar");

        $response->assertStatus(201);
        $this->assertDatabaseHas('practicas', ['id_alumno' => $alumno->id_alumno, 'id_oferta' => $oferta->id_oferta, 'estado' => 'SOLICITADA']);
    }
}