<?php

namespace Tests\Support\Database\Seeds;

use App\Controllers\Noticias; // Ajusta el namespace según tu estructura de directorios
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;



class PublicarTest extends CIUnitTestCase
{
    use ControllerTestTrait, DatabaseTestTrait;

    protected $seed = [
        'CategoriasSeeder',
        'UsuariosSeeder'
    ];

    public function testPublicar()
    {

        // Crear una instancia del modelo NoticiasModel
        $noticiasModel = new \App\Models\NoticiasModel();
    
        // Crear una noticia de ejemplo
        $noticia = [
            'version' => 1,
            'titulo' => 'Título de ejemplo',
            'descripcion' => 'Descripción de ejemplo',
            'imagen' => 'imagen.jpg',
            'estado' => 'estado_de_ejemplo',
            'activa' => true,
            'fechaPublicacion' => date('Y-m-d H:i:s'),
            'fechaExpiracion' => date('Y-m-d H:i:s', strtotime('+1 week')),
            'id_usuario' => 1, // ID de ejemplo de un usuario
            'id_categoria' => 1 // ID de ejemplo de una categoría
        ];
    
        // Insertar la noticia de ejemplo en la base de datos de prueba
        $noticiasModel->insert($noticia);
    
        // Obtener el ID de la noticia insertada
        $id = $noticiasModel->insertID();
    
        // Simular la solicitud POST con los datos necesarios utilizando $_POST
        $_POST['version'] = 1; // Simular la versión 1
    
        $controller = new Noticias($noticiasModel);
    
        // Simular la sesión de usuario
        $session = \Config\Services::session();
        $session->usuario = 'nombre_de_usuario'; // ajusta según tu usuario de sesión
    
        // Simular la llamada a la función publicar
        $response = $controller->publicar($id);
    
        // Verificar que la respuesta sea una redirección
        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $response);
    }
    
    
    
}
