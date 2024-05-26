<?php

namespace App\Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Mockery as m;
use CodeIgniter\Config\Services;

class AuthControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // Start the session manually
        $this->session = Services::session();
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testLoginValidationFails()
    {
        // Mock the request and response
        $request = Services::request();
        $response = Services::response();

        // Instantiate the controller with mock request and response
        $controller = new \App\Controllers\Usuarios();
        $controller->initController($request, $response, Services::logger());

        // Simulate a POST request with invalid data
        $_POST = [
            'nombre' => '',
            'pw' => ''
        ];

        // Call the login method
        $result = $controller->login();

        // Check the results
        $this->assertTrue($result instanceof \CodeIgniter\HTTP\RedirectResponse);
        $errors = implode(' ', session()->getFlashdata('_ci_validation_errors'));
        $this->assertStringContainsString('El campo Usuario es obligatorio', $errors);
        $this->assertStringContainsString('El campo Contraseña es obligatorio', $errors);
    }


    public function testLoginSuccess()
    {
        // Mock the request and response
        $request = Services::request();
        $response = Services::response();
    
        // Instantiate the controller with mock request and response
        $controller = new \App\Controllers\Usuarios();
        $controller->initController($request, $response, Services::logger());
    
        // Simulate a POST request with valid data
        $_POST = [
            'nombre' => 'testuser',
            'pw' => 'correctpassword'
        ];
    
        // Simulate validation success
        $_SESSION['_ci_validation_errors'] = [];
    
        // Mock the user model to return a fake user
        $mockUserModel = m::mock('App\Models\UsuariosModel');
        $mockUserModel->shouldReceive('find_by_name')
                      ->with('testuser')
                      ->andReturn(['nombre' => 'testuser', 'rol' => 1]);
    
        // Inject the mock into the service locator
        Services::injectMock('usuariosModel', $mockUserModel);
    
        // Call the login method
        $result = $controller->login();
    
        // Check that the session has been set correctly
        $this->assertEquals('testuser', session()->get('usuario'));
        $this->assertEquals(1, session()->get('rol'));
    
        // Check that the response is a redirect to the home page
        $this->assertTrue($result instanceof \CodeIgniter\HTTP\RedirectResponse);
        $this->assertEquals(base_url('/'), $result->getHeaderLine('Location'));
    }
    
    
    
}
