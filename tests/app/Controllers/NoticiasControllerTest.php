<?php

namespace Tests\App\Controllers;

use App\Controllers\Noticias; // Asegúrate de importar el controlador correcto
use CodeIgniter\HTTP\IncomingRequest;


use App\Database\Seeds\CategoriasSeeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;
use Config\Database;

class NoticiasControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait, DatabaseTestTrait;



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
    
        // Ejecutamos el método create del controlador
        $response = $controller->create();
    
        // Verificamos que la respuesta sea una redirección exitosa
        //$this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getHeaderLine('Location'));
    }
    
    
    
    

    public function testDraftRestriction()
    {
        $this->expectException('\CodeIgniter\Exceptions\PageNotFoundException');

        $noticias = new Noticias();

        // Simular una solicitud con usuario con 3 borradores ya creados
        $request = $this->createRequest('POST', 'noticias/create');
        $request->setJSON(['titulo' => 'Título', 'desc' => 'Descripción', /* Otros campos */]);
        $this->session(['usuario' => 'username']);
        $noticias->withRequest($request)->create();
    }

    public function testNewsInsertion()
    {
        $noticias = new Noticias();

        // Simular una solicitud con datos válidos
        $request = $this->createRequest('POST', 'noticias/create');
        $request->setJSON(['titulo' => 'Título', 'desc' => 'Descripción', /* Otros campos */]);
        $noticias->withRequest($request)->create();
    }
    

    public function testCreateImageUpload()
    {
        // Ruta del archivo de prueba
        $filePath = TESTPATH . '_data\test.jpg';

        // Verificar si el archivo existe
        $this->assertFileExists($filePath);

        $_FILES['archivo'] = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 500
        ];

        $csrfToken = csrf_hash();

        $result = $this->withSession([
            'usuario' => 'testUser'
        ])->post('noticias/create', [
            'csrf_test_name' => $csrfToken,
            'titulo' => 'Noticia de Prueba',
            'desc' => 'Descripción de prueba',
            'categoria' => '1',
            'estados' => '0'
        ]);

        $result->assertStatus(302);
        $result->assertRedirectTo('/');
    }
    

    // Más pruebas según sea necesario...
}
