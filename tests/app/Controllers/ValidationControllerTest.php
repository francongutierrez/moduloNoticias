<?php

namespace Tests\App\Controllers;

use App\Controllers\Noticias; // Asegúrate de importar el controlador correcto
use CodeIgniter\HTTP\IncomingRequest;


use App\Database\Seeds\CategoriasSeeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;
use Config\Database;

class ValidationControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait, DatabaseTestTrait;

    // For Migrations
    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = 'Tests\Support';

    // For Seeds
    protected $seedOnce = false;
    //protected $seed     = 'TestSeeder';
    protected $basePath = 'tests\_support\Database\Seeds';

    public function setUp(): void
    {
        parent::setUp();

        // Crear usuario de prueba
        $this->db->table('usuarios')->insert([
            'id' => 1,
            'nombre' => 'testUser',
            'contrasenia' => password_hash('password', PASSWORD_DEFAULT)
        ]);

        // Configurar CSRF para pruebas
        $_SESSION['csrf_test_name'] = bin2hex(random_bytes(32));
    }


    public function testCreateValidationPasses()
    {
        // Creamos una instancia del controlador
        $controller = new \App\Controllers\Noticias();
    
        // Simulamos una solicitud POST con datos válidos
        $_POST = [
            'titulo' => 'Título de prueba',
            'desc' => 'Descripción de prueba',
            'categoria' => '1',
            'estados' => '1'
        ];
    
        // Simulamos una sesión de usuario
        $_SESSION['usuario'] = 'testuser';


        $result = $this->withURI('http://localhost/modulonoticias/noticias/create')
        ->controller(Noticias::class)
        ->execute('create');

        $this->assertTrue($result->isOK());
    
    }
    


}
