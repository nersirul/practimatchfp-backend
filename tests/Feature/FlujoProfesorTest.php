<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\Profesor;
use App\Models\Practica;
use App\Models\Centro;
use App\Models\Alumno;
use App\Models\Empresa;
use App\Models\Oferta;
use App\Models\Administrador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class FlujoProfesorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de el profesor puede autorizar el inicio de una practica
     */
    public function test_el_profesor_puede_autorizar_el_inicio_de_una_practica()
    {
        $admin = Administrador::create(['nombre' => 'A', 'email' => 'a@a.com', 'password' => '123']);
        $centro = Centro::create(['nombre' => 'IES Test']);
        $empresa = Empresa::create(['nombre_comercial' => 'E', 'cif' => 'B11111111', 'email_contacto' => 'e@e.com', 'password' => '123', 'sector' => 'IT', 'telefono_contacto' => '1', 'direccion' => 'd', 'ciudad' => 'c', 'activa' => true]);
        $oferta = Oferta::create(['id_empresa' => $empresa->id_empresa, 'id_admin_validador' => $admin->id_admin, 'titulo' => 'T', 'descripcion' => 'D', 'modalidad' => 'REMOTO', 'es_remunerada' => 1, 'posibilidad_contratacion' => 1, 'estado' => 'PUBLICADA', 'vacantes' => 1, 'activa' => 1]);
        
        // 1. Creamos primero al Profesor
        $profesor = Profesor::create(['id_centro' => $centro->id_centro, 'nombre' => 'Prof', 'apellidos' => 'Test', 'email' => 'p@p.com', 'password' => '123', 'telefono' => '123', 'departamento' => 'IT']);
        
        // 2. Creamos al Alumno y LE ASIGNAMOS ESE PROFESOR COMO TUTOR
        $alumno = Alumno::create(['id_centro' => $centro->id_centro, 'id_profesor' => $profesor->id_profesor, 'nombre' => 'A', 'apellidos' => 'B', 'nif' => '11111111A', 'email' => 'al@al.com', 'password' => '123', 'ciclo' => 'DAW', 'modalidad_preferida' => 'REMOTO', 'telefono' => '1', 'direccion' => 'd', 'ciudad' => 'c']);
        
        $practica = Practica::create([
            'id_alumno' => $alumno->id_alumno,
            'id_oferta' => $oferta->id_oferta,
            'id_profesor' => $profesor->id_profesor,
            'estado' => 'ESPERANDO_TUTOR'
        ]);

        Sanctum::actingAs($profesor, ['*']);

        $response = $this->postJson("/api/profesor/practicas/{$practica->id_practica}/aprobar");
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('practicas', ['id_practica' => $practica->id_practica, 'estado' => 'EN_CURSO']);
    }
}