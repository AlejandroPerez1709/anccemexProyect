<?php
// app/controllers/UsuariosController.php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../config/config.php';

class UsuariosController {

    public function index() {
        check_permission('superusuario'); 
        
        $searchTerm = '';
        if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
            $searchTerm = trim($_GET['search']);
        }

        $usuarios = User::getAll($searchTerm);

        $pageTitle = 'Listado de Usuarios';
        $currentRoute = 'usuarios_index';
        $contentView = __DIR__ . '/../views/usuarios/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function create() {
        check_permission('superusuario'); 
        
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);

        $pageTitle = 'Registrar Nuevo Usuario';
        $currentRoute = 'usuarios/create';
        $contentView = __DIR__ . '/../views/usuarios/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function store() {
        check_permission('superusuario');
        
        $_SESSION['form_data'] = $_POST;
        if(isset($_POST)) {
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
                'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'username' => trim($_POST['username'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'rol' => trim($_POST['rol'] ?? ''),
                'estado' => trim($_POST['estado'] ?? '')
            ];

            if(User::store($data)) {
                $_SESSION['message'] = "Usuario creado exitosamente.";
                unset($_SESSION['form_data']);
                header("Location: index.php?route=usuarios_index");
                exit;
            } else {
                $_SESSION['error'] = "Error al crear el usuario: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=usuarios/create");
                exit; 
            }
        }
    }

    public function edit($id = null) {
        check_permission('superusuario');
        $usuarioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($usuarioId) {
            $usuario = User::getById($usuarioId);
            if($usuario) {
                $formData = $_SESSION['form_data'] ?? $usuario;
                unset($_SESSION['form_data']);

                $pageTitle = 'Editar Usuario';
                $currentRoute = 'usuarios/edit';
                $contentView = __DIR__ . '/../views/usuarios/edit.php';
                require_once __DIR__ . '/../views/layouts/master.php';
                return;
            }
        }
        $_SESSION['error'] = "Usuario no encontrado.";
        header("Location: index.php?route=usuarios_index");
        exit;
    }

    public function update() {
        check_permission('superusuario');
        
        $id = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de usuario inválido.";
            header("Location: index.php?route=usuarios_index"); 
            exit;
        }
        
        $_SESSION['form_data'] = $_POST;
        if(isset($_POST)) {
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
                'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'username' => trim($_POST['username'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'rol' => trim($_POST['rol'] ?? ''),
                'estado' => trim($_POST['estado'] ?? '')
            ];

            if(User::update($id, $data)) {
                $_SESSION['message'] = "Usuario actualizado exitosamente.";
                unset($_SESSION['form_data']);
                header("Location: index.php?route=usuarios_index");
                exit;
            } else {
                $_SESSION['error'] = "Error al actualizar el usuario: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=usuarios/edit&id=" . $id);
                exit;
            }
        }
    }

    public function delete($id = null) {
        check_permission('superusuario'); 

        $usuarioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $razon = $_POST['razon'] ?? '';

        // Se mantiene la validación para no desactivarse a sí mismo
        if (isset($_SESSION['user']['id_usuario']) && $usuarioId == $_SESSION['user']['id_usuario']) {
            $_SESSION['error'] = "No puedes desactivar tu propio usuario.";
            header("Location: index.php?route=usuarios_index");
            exit;
        }

        if (empty($razon)) {
            $_SESSION['error'] = "La razón de desactivación es obligatoria.";
            header("Location: index.php?route=usuarios_index");
            exit;
        }

        if($usuarioId) {
            if(User::delete($usuarioId, $razon)) {
                $_SESSION['message'] = "Usuario desactivado exitosamente.";
            } else {
                $_SESSION['error'] = "Error al desactivar el usuario.";
            }
        } else {
            $_SESSION['error'] = "ID de usuario no especificado.";
        }
        header("Location: index.php?route=usuarios_index");
        exit;
    }
}