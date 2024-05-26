<?php

namespace Tests\Controllers;

use CodeIgniter\Test\FeatureTestTrait;
use App\Controllers\Noticias;

class IndexTest extends \CodeIgniter\Test\CIUnitTestCase
{
    use FeatureTestTrait;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testIndexWithNullSessionRole()
    {
        // Creamos una instancia del controlador
        $noticiasController = new Noticias();
    
        // Configuramos la sesión con rol nulo
        session()->rol = null;
    
        // Ejecutamos el método index del controlador
        $result = $noticiasController->index();
    
        // Verificamos que la respuesta contenga el diseño base
        $this->assertStringContainsString('layouts/layoutBase', (string)$result);
    }
    
    public function testIndexWithEditorSessionRole()
    {
        // Creamos una instancia del controlador
        $noticiasController = new Noticias();

        // Configuramos la sesión con rol nulo
        session()->rol = EDITOR;

        // Ejecutamos el método index del controlador
        $result = $noticiasController->index();

        // Verificamos que la respuesta contenga el diseño correspondiente al editor
        $this->assertStringContainsString('layouts/layoutEditor', (string)$result);
    }

    // Puedes escribir más pruebas similares para otros roles de sesión

}
