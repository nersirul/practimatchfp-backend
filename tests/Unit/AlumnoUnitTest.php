<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Alumno;

class AlumnoUnitTest extends TestCase
{
    /**
     * Comprueba que el Accessor del nombre completo concatena correctamente.
     */
    public function test_obtiene_el_nombre_completo_formateado()
    {
        // 1. Setup: Instanciamos en memoria (sin base de datos)
        $alumno = new Alumno();
        $alumno->nombre = 'María';
        $alumno->apellidos = 'García López';

        // 2. Acción & 3. Comprobación
        $this->assertEquals('María García López', $alumno->nombre_completo);
    }

    /**
     * Comprueba que detecta correctamente si tiene un tutor asignado.
     */
    public function test_alumno_tiene_tutor_asignado()
    {
        $alumno = new Alumno();
        $alumno->id_profesor = 5; // Le asignamos un ID falso de profesor

        $this->assertTrue($alumno->tieneTutorAsignado());
    }

    /**
     * Comprueba que detecta correctamente si es un alumno huérfano (sin tutor).
     */
    public function test_alumno_no_tiene_tutor_asignado_si_es_null()
    {
        $alumno = new Alumno();
        $alumno->id_profesor = null;

        $this->assertFalse($alumno->tieneTutorAsignado());
    }
}