<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SeguimientosModel;
use App\Models\CategoriasModel;
use App\Models\NoticiasModel;
use App\Models\UsuariosModel;
use App\Models\RespaldosModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Noticias extends BaseController
{
    private $usuariosModel;
    private $categoriasModel;
    private $noticiasModel;
    private $seguimientosModel;
    private $respaldosModel;

    protected $helpers = ['form'];

    public function __construct()
    {
        $this->usuariosModel = new UsuariosModel();
        $this->categoriasModel = new CategoriasModel();
        $this->noticiasModel = new NoticiasModel();
        $this->seguimientosModel = new SeguimientosModel();
        $this->respaldosModel = new RespaldosModel();
    }

    //? ---------------------------------------------- CRUD -----------------------------------------------

    //?----------------------------------------vistas de noticias------------------------------------------
    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        //? Muestra de todas las noticias publicadas
        $noticias = $this->noticiasModel->noticiasPublicadas();

        if ($this->session->rol === null) {
            return view('Noticias/index', ['noticias' => $noticias, 'titulo' => 'Noticias publicadas', 'layout' => 'layouts/layoutBase']);
        } elseif ($this->session->rol === EDITOR) {
            return view('Noticias/index', ['noticias' => $noticias, 'titulo' => 'Noticias publicadas', 'layout' => 'layouts/layoutEditor']);
        } elseif ($this->session->rol === VALIDADOR) {
            return view('Noticias/index', ['noticias' => $noticias, 'titulo' => 'Noticias publicadas', 'layout' => 'layouts/layoutValidador']);
        } elseif ($this->session->rol === AMBOS) {
            return view('Noticias/index', ['noticias' => $noticias, 'titulo' => 'Noticias publicadas', 'layout' => 'layouts/layoutMultiRol']);
        }
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        $noticia = $this->noticiasModel->noticiaCategoria($id);


        if (empty($noticia)) {
            throw new PageNotFoundException('Cannot find the news item: ' . $id);
        }


        if ($this->session->rol === null) {
            return view('Noticias/view', ['noticia' => $noticia, 'titulo' => $noticia['titulo'], 'layout' => 'layouts/layoutBase']);
        } elseif ($this->session->rol === EDITOR) {
            return view('Noticias/view', ['noticia' => $noticia, 'titulo' => $noticia['titulo'], 'layout' => 'layouts/layoutEditor']);
        } elseif ($this->session->rol === VALIDADOR) {
            return view('Noticias/view', ['noticia' => $noticia, 'titulo' => $noticia['titulo'], 'layout' => 'layouts/layoutValidador']);
        } elseif ($this->session->rol === AMBOS) {
            return view('Noticias/view', ['noticia' => $noticia, 'titulo' => $noticia['titulo'], 'layout' => 'layouts/layoutMultiRol']);
        }
    }
    //? -------------------------------------Fin de vistas de noticias-----------------------------------------------

    //?---------------------------- crear noticias--------------------------------------------------------

    /**
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface
     */
    public function new()
    {
        //TODO: crear una una noticia

        if ($this->session->rol === null) {
            return redirect()->to('/');
        } elseif ($this->session->rol === EDITOR) {
            return view('Noticias/new', ['categorias' => $this->categoriasModel->findAll(), 'titulo' => 'Crear noticia', 'layout' => 'layouts/layoutEditor']);
        } elseif ($this->session->rol === VALIDADOR) {
            return redirect()->to('/');
        } elseif ($this->session->rol === AMBOS) {
            return view('Noticias/new', ['categorias' => $this->categoriasModel->findAll(), 'titulo' => 'Crear noticia', 'layout' => 'layouts/layoutMultiRol']);
        }
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        //? validación e inserción de la noticia

        $reglas = [
            'titulo' => [
                'label' => 'Titulo',
                'rules' => 'required|max_length[150]',
                'errors' => [
                    'required' => 'El campo {field} es obligatorio',
                    'max_length' => 'El campo {field} no puede exceder los {param} caracteres.'
                ]
            ],

            'desc' => [
                'label' => 'Descripción',
                'rules' => 'required|max_length[1000]',
                'errors' => [
                    'required' => 'El campo {field} es obligatorio',
                    'max_length' => 'El campo {field} no puede exceder los {param} caracteres.'
                ]
            ],

            'archivo' => [
                'label' => 'Selecciona una imagen',
                'rules' => [
                    'is_image[archivo]',
                    'mime_in[archivo,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                    'max_size[archivo,1000]',
                    'max_dims[archivo,1920,1080]',
                ],
                'errors' => [
                    'is_image' => 'El campo {field} debe ser una imagen',
                    'mime_in' => 'Los formatos soportados son jpg/jpeg/png',
                    'max_size' => 'El tamaño maximo es 1 mb',
                    'max_dims' => 'Las dimensiones maximas son 1920x1080'
                ]
            ],

            'categoria' => [
                'label' => 'Seleccione una categoria',
                'rules' => 'required|in_list[1,2,3,4,5]',
                'errors' => [
                    'required' => 'El campo {field} es obligatorio',
                ]
            ],

            'estados' => [
                'label' => 'Seleccione una opción',
                'rules' => 'required|in_list[0,1]',
                'errors' => [
                    'required' => 'El campo {field} es obligatorio',
                ]
            ]
        ];

        if (!$this->validate($reglas)) {
            return redirect()->back()->withInput('error', $this->validator->listErrors());
        }

        $file = $this->request->getFile('archivo');

        if (!$file->hasMoved() && $file->isValid() && $file->getSize() > 0) {
            $newName = $file->getRandomName();
            $filepath = ROOTPATH . 'public/uploads/';
            $file->move($filepath, $newName);
        } else {
            $newName = '';
        }

        $usuario = $this->session->usuario;
        $usuarioBuscado = $this->usuariosModel->find_by_name($usuario);

        $post = $this->request->getPost(['titulo', 'desc', 'categoria', 'estados']);

        if (intval($post['estados']) === BORRADOR) {
            $user = $this->session->usuario;
            $borrador = $this->noticiasModel->noticiasPorEstado($user, BORRADOR);

            if (count($borrador) >= 3) {
                return redirect()->back()->with('error', 'No puede agregar la noticia a borrador debido a que ya tiene tres en su borrador. Desactive o descarte una noticia.');
            }
        }


        $this->noticiasModel->insert([
            'version' => 0,
            'titulo' => trim($post['titulo']),
            'descripcion' => trim($post['desc']),
            'estado' => intval($post['estados']),
            'imagen' => $newName,
            'activa' => 1,
            'id_categoria' => intval($post['categoria']),
            'id_usuario' => $usuarioBuscado['id']
        ]);
        return redirect()->to('/');
    }

    //? -------------------------------------Fin de crear noticias-----------------------------------------------

    //?------------------------------------modificar noticias---------------------------------------------

    /**
     * Return the editable properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function edit($id = null)
    {
        //? Editar una noticia seleccionada

        if ($this->session->rol === null) {
            return redirect()->to('/');
        } elseif ($this->session->rol === EDITOR) {
            return view('Noticias/edit', ['categorias' => $this->categoriasModel->findAll(), 'noticia' => $this->noticiasModel->find($id), 'titulo' => 'Editar noticia', 'layout' => 'layouts/layoutEditor']);
        } elseif ($this->session->rol === VALIDADOR) {
            return redirect()->to('/');
        } elseif ($this->session->rol === AMBOS) {
            return view('Noticias/edit', ['categorias' => $this->categoriasModel->findAll(), 'noticia' => $this->noticiasModel->find($id), 'titulo' => 'Editar noticia', 'layout' => 'layouts/layoutMultiRol']);
        }
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {
        //? validación y modificación de la noticia

        if (!$this->request->is('put') || $id == null) {
            return redirect()->route('/');
        }

        //? traemos la noticia a partir del $id traido del formulario

        $noticia = $this->noticiasModel->find($id);
        $version = intval($noticia['version']);

        if (intval($this->request->getPost('version')) !== $version) {
            return redirect()->back()->with('error', 'Por favor, vuelva al área de trabajo para verificar posibles actualizaciones en la noticia. Para obtener más detalles, consulte el seguimiento de la misma.');
        }

        //? validando el formulario
        $reglas = [
            'titulo' => [
                'label' => 'Titulo',
                'rules' => 'required|max_length[150]',
                'errors' => [
                    'required' => 'El campo {field} es obligatorio',
                    'max_length' => 'El campo {field} no puede exceder los {param} caracteres.'
                ]
            ],

            'desc' => [
                'label' => 'Descripción',
                'rules' => 'required|max_length[1000]',
                'errors' => [
                    'required' => 'El campo {field} es obligatorio',
                    'max_length' => 'El campo {field} no puede exceder los {param} caracteres.'
                ]
            ],

            'archivo' => [
                'label' => 'Selecciona una imagen',
                'rules' => [
                    'is_image[archivo]',
                    'mime_in[archivo,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                    'max_size[archivo,1000]',
                    'max_dims[archivo,1920,1080]',
                ],
                'errors' => [
                    'is_image' => 'El campo {field} debe ser una imagen',
                    'mime_in' => 'Los formatos soportados son jpg/jpeg/png',
                    'max_size' => 'El tamaño maximo es 1 mb',
                    'max_dims' => 'Las dimensiones maximas son 1920x1080'
                ]
            ],

            'categoria' => [
                'label' => 'Seleccione una categoria',
                'rules' => 'required|in_list[1,2,3,4,5]',
                'errors' => [
                    'required' => 'El campo {field} es obligatorio',
                ]
            ],

            'estados' => [
                'label' => 'Seleccione una opción',
                'rules' => 'required|in_list[0,1]',
                'errors' => [
                    'required' => 'El campo {field} es obligatorio',
                ]
            ]
        ];

        if (!$this->validate($reglas)) {
            return redirect()->back()->withInput('error', $this->validator->listErrors());
        }

        //? trabajando la imagen.
        $file = $this->request->getFile('archivo');

        if (!$file->hasMoved() && $file->isValid() && $file->getSize() > 0) {
            $newName =  $file->getRandomName();
            $filepath = ROOTPATH . 'public/uploads/';
            $file->move($filepath, $newName);
        } else {
            $newName = $noticia['imagen'];
        }

        //? comprobamos que el borrador no este ocupado

        $post = $this->request->getPost(['titulo', 'desc', 'categoria', 'estados']);
        $version = $version + 1;

        if (intval($post['estados']) === BORRADOR) {
            $user = $this->session->usuario;
            $borrador = $this->noticiasModel->noticiasPorEstado($user, BORRADOR);

            if (count($borrador) >= 3) {
                return redirect()->back()->with('error', 'No puede agregar la noticia a borrador debido a que ya tiene tres en su borrador. Desactive o descarte una noticia.');
            }
        }

        //? crear el respaldo o modificar el respaldo de ser necesario

        $res = $this->respaldosModel->respaldoNoticia($noticia['id']);

        if (count($res) > 0) {
            $respaldo = $res[0];
            $this->respaldosModel->update($respaldo['id'], [
                'titulo' => $noticia['titulo'],
                'descripcion' => $noticia['descripcion'],
                'estado' => $noticia['estado'],
                'imagen' => $noticia['imagen'],
                'id_categoria' => $noticia['id_categoria'],
                'activa' => $noticia['activa'],
                'fechaPublicacion' => $noticia['fechaPublicacion'],
                'fechaExpiracion' => $noticia['fechaExpiracion']
            ]);
        } else {

            $this->respaldosModel->insert([
                'titulo' => $noticia['titulo'],
                'descripcion' => $noticia['descripcion'],
                'estado' => $noticia['estado'],
                'imagen' => $noticia['imagen'],
                'id_categoria' => $noticia['id_categoria'],
                'activa' => $noticia['activa'],
                'fechaPublicacion' => $noticia['fechaPublicacion'],
                'fechaExpiracion' => $noticia['fechaExpiracion'],
                'id_noticia' => $noticia['id']
            ]);
        }

        //? haciendo la modificación de la noticia

        $this->noticiasModel->update($id, [
            'version' => $version,
            'titulo' => trim($post['titulo']),
            'descripcion' => trim($post['desc']),
            'estado' => intval($post['estados']),
            'imagen' => $newName,
            'id_categoria' => intval($post['categoria']),
        ]);

        //? creamos el seguimiento
        $noticiaCat = $this->noticiasModel->noticiaCategoria($noticia['id']);

        $tit = $noticiaCat['titulo'];
        $desc = $noticiaCat['descripcion'];
        $est = $noticiaCat['estado'];
        $img = $noticiaCat['imagen'];
        $categoria = $noticiaCat['categoria'];
        $antes = "$tit|$desc|$est|$img|$categoria";

        $tit = trim($post['titulo']);
        $desc = trim($post['desc']);
        $est = intval($post['estados']);
        $img = $newName;
        $categoria = intval($post['categoria']);
        $despues = "$tit|$desc|$est|$img|$categoria";
        $usuario = $this->usuariosModel->find_by_name($this->session->usuario);

        $this->seguimientosModel->insert([
            'accion' => MODIFICO,
            'antes' => $antes,
            'despues' => $despues,
            'id_usuario' => $usuario['id'],
            'id_noticia' => $noticia['id']
        ]);

        return redirect()->to('noticias/home');
    }
    //? -------------------------------------Fin de modificar noticias-----------------------------------------------

    //?--------------------------------------borrar noticias--------------------------------------------

    public function delete($id = null)
    {
        if (!$this->request->is('delete') || $id == null) {
            return redirect()->route('noticias/home');
        }

        $this->noticiasModel->delete($id);

        return redirect()->to('noticias/home');
    }
    //? -------------------------------------Fin de borrar noticias-----------------------------------------------
    //? ------------------------------------Fin del CRUD--------------------------------------------------

    //*------------------------ Requerimentos ------------------------------------------------

    //* ---------------------------Vistas-----------------------------

    public function home()
    {
        //* muestra el área de trabajo de los usuario editor y multirol.
        $user = $this->session->usuario;

        $data = [
            'titulo' => 'Área de trabajo',
            'borrador' => $this->noticiasModel->noticiasPorEstado($user, BORRADOR),
            'validacion' => $this->noticiasModel->noticiasPorEstado($user, L_VALIDAR),
            'corregir' => $this->noticiasModel->noticiasPorEstado($user, CORREGIR),
            'rechazadas' => $this->noticiasModel->noticiasPorEstado($user, RECHAZADO),
            'desactivadas' => $this->noticiasModel->noticiasDesactivadas($user),
            'publicadas' => $this->noticiasModel->noticiasPublicadasUser($user),
        ];
        $editor = $data;
        $editor['layout'] = 'layouts/layoutEditor';
        $multiRol = $data;
        $multiRol['layout'] = 'layouts/layoutMultiRol';

        if ($this->session->rol === null) {
            return redirect()->to('/');
        } elseif ($this->session->rol === EDITOR) {
            return view('Noticias/home', $editor);
        } elseif ($this->session->rol === VALIDADOR) {
            return redirect()->to('/');
        } elseif ($this->session->rol === AMBOS) {
            return view('Noticias/home', $multiRol);
        }
    }

    public function validates()
    {
        //* muestra el área de trabajo de los usuarios validador y multirol
        $user = $this->session->usuario;
        $data = [
            'titulo' => 'Área de validación',
            'validar' => $this->noticiasModel->noticiasAValidarUser($user),
            'sinValidar' => $this->noticiasModel->noticiasPublicadasSinValidar(),
            'seguimientos' => $this->seguimientosModel->seguimientosNoticiasUser($user)
        ];
        $validador = $data;
        $validador['layout'] = 'layouts/layoutValidador';
        $multiRol = $data;
        $multiRol['layout'] = 'layouts/layoutMultiRol';

        if ($this->session->rol === null) {
            return redirect()->to('/');
        } elseif ($this->session->rol === EDITOR) {
            return redirect()->to('/');
        } elseif ($this->session->rol === VALIDADOR) {
            return view('Noticias/validate', $validador);
        } elseif ($this->session->rol === AMBOS) {
            return view('Noticias/validate', $multiRol);
        }
    }

    //*------------------------------Fin de Vistas----------------------------------------

    //*-----------------------------Procesos---------------------------------

    public function deshacerModificacion($idNoticia)
    {
        $noticia = $this->noticiasModel->find($idNoticia);
        $noticiaCat = $this->noticiasModel->noticiaCategoria($idNoticia);


        $version = intval($noticia['version']);
        if ($version > 0) {
            $res = $this->respaldosModel->respaldoNoticia($idNoticia);
            $respaldo = $res[0];

            //* volvemos la modificación
            $version = $version + 1;
            $this->noticiasModel->update($idNoticia, [
                'version' => $version,
                'titulo' => $respaldo['titulo'],
                'descripcion' => $respaldo['descripcion'],
                'estado' => $respaldo['estado'],
                'imagen' => $respaldo['imagen'],
                'id_categoria' => $respaldo['id_categoria'],
                'activa' => $respaldo['activa'],
                'fechaPublicacion' => $respaldo['fechaPublicacion'],
                'fechaExpiracion' => $respaldo['fechaExpiracion']
            ]);

            //* cargamos el respaldo con los valores previos al deshacer

            $this->respaldosModel->update($respaldo['id'], [
                'titulo' => $noticia['titulo'],
                'descripcion' => $noticia['descripcion'],
                'estado' => $noticia['estado'],
                'imagen' => $noticia['imagen'],
                'id_categoria' => $noticia['id_categoria'],
                'activa' => $noticia['activa'],
                'fechaPublicacion' => $noticia['fechaPublicacion'],
                'fechaExpiracion' => $noticia['fechaExpiracion']
            ]);
        }

        //* creamos el seguimiento
        $usuario = $this->usuariosModel->find_by_name($this->session->usuario);



        $tit = $noticiaCat['titulo'];
        $desc = $noticiaCat['descripcion'];
        $est = $noticiaCat['estado'];
        $img = $noticiaCat['imagen'];
        $categoria = $noticiaCat['categoria'];
        $despues = "$tit|$desc|$est|$img|$categoria";
        $noticiaCat = $this->noticiasModel->noticiaCategoria($idNoticia);

        $tit = $noticiaCat['titulo'];
        $desc = $noticiaCat['descripcion'];
        $est = $noticiaCat['estado'];
        $img = $noticiaCat['imagen'];
        $categoria = $noticiaCat['categoria'];
        $antes = "$tit|$desc|$est|$img|$categoria";
        $usuario = $this->usuariosModel->find_by_name($this->session->usuario);

        $this->seguimientosModel->insert([
            'accion' => DESHIZO,
            'antes' => $antes,
            'despues' => $despues,
            'id_usuario' => $usuario['id'],
            'id_noticia' => $noticia['id']
        ]);

        return redirect()->to('noticias/home');
    }

    public function desactivar($id)
    {
        $noticia = $this->noticiasModel->find($id);
        $version = intval($noticia['version']);

        if (intval($this->request->getPost('version')) !== $version) {
            return redirect()->back()->with('error', 'Por favor, recargue el área de trabajo para verificar posibles actualizaciones en la noticia. Para obtener más detalles, consulte el seguimiento de la misma.');
        }

        $this->noticiasModel->update($id, [
            'activa' => DESACTIVADA
        ]);

        return redirect()->to('noticias/home');
    }

    public function activar($id)
    {
        $user = $this->session->usuario;
        $borrador = $this->noticiasModel->noticiasPorEstado($user, BORRADOR);

        if (count($borrador) >= 3) {
            return redirect()->back()->with('error', 'No puede activar la noticia mientras tenga tres noticias en su borrador.');
        }

        $this->noticiasModel->update($id, [
            'activa' => ACTIVA
        ]);

        return redirect()->to('noticias/home');
    }

    public function enviarABorrador($id)
    {
        $noticia = $this->noticiasModel->find($id);
        $version = intval($noticia['version']);

        if (intval($this->request->getPost('version')) !== $version) {
            return redirect()->back()->with('error', 'Por favor, recargue el área de trabajo para verificar posibles actualizaciones en la noticia. Para obtener más detalles, consulte el seguimiento de la misma.');
        }
        
        $user = $this->session->usuario;
        $borrador = $this->noticiasModel->noticiasPorEstado($user, BORRADOR);

        if (count($borrador) >= 3) {
            return redirect()->back()->with('error', 'No puede agregar la noticia a borrador debido a que ya tiene tres en su borrador. Desactive o descarte una noticia.');
        }

        $this->noticiasModel->update($id, [
            'estado' => BORRADOR
        ]);

        $usuario = $this->usuariosModel->find_by_name($this->session->usuario);

        $this->seguimientosModel->insert([
            'accion' => MODIFICO,
            'antes' => 'validandose',
            'despues' => 'borrador',
            'id_usuario' => $usuario['id'],
            'id_noticia' => $id
        ]);

        return redirect()->to('noticias/home');
    }

    public function enviarAValidar($id)
    {
        $noticia = $this->noticiasModel->find($id);
        $version = intval($noticia['version']);

        if (intval($this->request->getPost('version')) !== $version) {
            return redirect()->back()->with('error', 'Por favor, recargue el área de trabajo para verificar posibles actualizaciones en la noticia. Para obtener más detalles, consulte el seguimiento de la misma.');
        }
        

        $this->noticiasModel->update($id, [
            'estado' => L_VALIDAR
        ]);

        $usuario = $this->usuariosModel->find_by_name($this->session->usuario);

        $this->seguimientosModel->insert([
            'accion' => MODIFICO,
            'antes' => 'borrador',
            'despues' => 'validandose',
            'id_usuario' => $usuario['id'],
            'id_noticia' => $id
        ]);

        return redirect()->to('noticias/home');
    }



    //*------------------------------Fin de Procesos----------------------------------------

    //*------------------------------Fin de Requrimentos----------------------------------------
}
