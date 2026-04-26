<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Oferta;

class OfertaUnitTest extends TestCase
{
    /**
     * Comprueba que una oferta activa y con vacantes devuelve TRUE.
     */
    public function test_oferta_tiene_plazas_si_esta_activa_y_con_vacantes()
    {
        // 1. Setup: Instanciamos el modelo en memoria (Sin tocar Base de Datos)
        $oferta = new Oferta();
        $oferta->activa = true;
        $oferta->vacantes = 3;

        // 2. Acción y 3. Comprobación
        $this->assertTrue($oferta->tienePlazasDisponibles());
    }

    /**
     * Comprueba que una oferta sin vacantes devuelve FALSE.
     */
    public function test_oferta_no_tiene_plazas_si_vacantes_es_cero()
    {
        $oferta = new Oferta();
        $oferta->activa = true;
        $oferta->vacantes = 0;

        $this->assertFalse($oferta->tienePlazasDisponibles());
    }

    /**
     * Comprueba que una oferta pausada devuelve FALSE aunque tenga vacantes.
     */
    public function test_oferta_no_tiene_plazas_si_esta_pausada()
    {
        $oferta = new Oferta();
        $oferta->activa = false;
        $oferta->vacantes = 5;

        $this->assertFalse($oferta->tienePlazasDisponibles());
    }
}