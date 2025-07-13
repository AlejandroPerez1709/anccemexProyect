<?php
// app/controllers/UsuariosController.php
require_once __DIR__ . '/../models/User.php'; // Asegúrate que la ruta al modelo User es correcta

class UsuariosController {

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function create() {
        session_start();
        if(!isset($_SESSION['user'])){
            header("Location: index.php?route=login");
            exit;
        }
        // Solo superusuarios pueden crear otros usuarios (ejemplo de control de rol)
        if ($_SESSION['user']['rol'] !== 'superusuario') {
             $_SESSION['error'] = "No tienes permisos para registrar usuarios.";
             header("Location: index.php?route=dashboard"); // O a donde sea apropiado
             exit;
        }


        $pageTitle = 'Registrar Nuevo Usuario';
        $currentRoute = 'usuarios/create'; // Para marcar el menú activo
        $contentView = __DIR__ . '/../views/usuarios/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Procesa los datos del formulario de creación y guarda el nuevo usuario.
     */
    public function store() {
        session_start();
        if(!isset($_SESSION['user'])){
            header("Location: index.php?route=login");
            exit;
        }
        // Solo superusuarios pueden crear otros usuarios
        if ($_SESSION['user']['rol'] !== 'superusuario') {
             $_SESSION['error'] = "No tienes permisos para registrar usuarios.";
             header("Location: index.php?route=dashboard");
             exit;
        }


        if(isset($_POST)) {
            // Validaciones del lado del servidor
            $nombre = trim($_POST['nombre']);
            $apellido_paterno = trim($_POST['apellido_paterno']);
            $apellido_materno = trim($_POST['apellido_materno']);
            $email = trim($_POST['email']);
            $username = trim($_POST['username']);
            $password = trim($_POST['password']); // No trimear passwords
            $rol = trim($_POST['rol']);
            $estado = trim($_POST['estado']);

            // --- Realizar Validaciones ---

            // Validación de campos vacíos básicos
            if (empty($nombre) || empty($apellido_paterno) || empty($apellido_materno) || empty($email) || empty($username) || empty($password) || empty($rol) || empty($estado)) {
                 $_SESSION['error'] = "Todos los campos marcados con * son obligatorios.";
                 header("Location: index.php?route=usuarios/create");
                 exit;
            }


            // Validación de nombres (solo letras y espacios)
            if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre) ||
                !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno) ||
                !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) {
                $_SESSION['error'] = "Los campos de nombre y apellidos solo deben contener letras y espacios.";
                header("Location: index.php?route=usuarios/create");
                exit;
            }

            // Validación de email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "El email no tiene un formato válido.";
                header("Location: index.php?route=usuarios/create");
                exit;
            }

            // Validación de username (ej: alfanumérico, longitud mínima)
             if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
                 $_SESSION['error'] = "El nombre de usuario debe tener entre 4 y 20 caracteres alfanuméricos o guión bajo (_).";
                 header("Location: index.php?route=usuarios/create");
                 exit;
             }

            // Validación de contraseña (ej: longitud mínima)
            if (strlen($password) < 6) { // Ejemplo: mínimo 6 caracteres
                 $_SESSION['error'] = "La contraseña debe tener al menos 6 caracteres.";
                 header("Location: index.php?route=usuarios/create");
                 exit;
             }

            // Validación de rol
            $roles_permitidos = ['superusuario', 'usuario'];
            if (!in_array($rol, $roles_permitidos)) {
                $_SESSION['error'] = "El rol seleccionado no es válido.";
                header("Location: index.php?route=usuarios/create");
                exit;
            }

            // Validación de estado
            $estados_permitidos = ['activo', 'inactivo'];
            if (!in_array($estado, $estados_permitidos)) {
                $_SESSION['error'] = "El estado seleccionado no es válido.";
                header("Location: index.php?route=usuarios/create");
                exit;
            }

            // --- Fin Validaciones ---

            $data = [
                'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'email' => $email,
                'username' => $username,
                'password' => $password, // Se hashea en el modelo
                'rol' => $rol,
                'estado' => $estado
            ];

            if(User::store($data)) {
                $_SESSION['message'] = "Usuario creado exitosamente.";
            } else {
                // El modelo debería loguear el error específico.
                // Podríamos intentar obtener el error de la base de datos si es un duplicado.
                 $_SESSION['error'] = "Error al crear el usuario. Es posible que el email o username ya existan.";
                 // Para evitar perder los datos del formulario, podrías guardarlos en sesión y redirigir de nuevo al create
                 header("Location: index.php?route=usuarios/create"); // Redirige de nuevo al formulario en caso de error
                 exit;
            }
        } else {
             $_SESSION['error'] = "No se recibieron datos del formulario.";
        }
        // Redirigir al listado si todo fue bien
        header("Location: index.php?route=usuarios_index");
        exit;
    }

    /**
     * Muestra la lista de todos los usuarios.
     */
    public function index() {
        session_start();
        if(!isset($_SESSION['user'])){
            header("Location: index.php?route=login");
            exit;
        }
        // Solo superusuarios pueden ver la lista completa (ejemplo)
         if ($_SESSION['user']['rol'] !== 'superusuario') {
             $_SESSION['error'] = "No tienes permisos para ver la lista de usuarios.";
             header("Location: index.php?route=dashboard");
             exit;
         }

        $usuarios = User::getAll(); // Obtiene todos los usuarios desde el modelo

        $pageTitle = 'Listado de Usuarios';
        $currentRoute = 'usuarios_index'; // Para marcar el menú activo
        $contentView = __DIR__ . '/../views/usuarios/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Muestra el formulario para editar un usuario existente.
     */
    public function edit() {
        session_start();
        if(!isset($_SESSION['user'])){
            header("Location: index.php?route=login");
            exit;
        }
         // Solo superusuarios pueden editar (ejemplo)
         if ($_SESSION['user']['rol'] !== 'superusuario') {
             $_SESSION['error'] = "No tienes permisos para editar usuarios.";
             header("Location: index.php?route=dashboard");
             exit;
         }


        if(isset($_GET['id'])) {
            $id = intval($_GET['id']);

            // Evitar que un superusuario se edite a sí mismo si hay reglas especiales
            // if ($id === $_SESSION['user']['id_usuario']) {
            //    $_SESSION['error'] = "No puedes editar tu propio usuario desde aquí.";
            //    header("Location: index.php?route=usuarios_index");
            //    exit;
            // }

            $usuario = User::getById($id); // Obtiene el usuario por ID desde el modelo

            if($usuario) {
                $pageTitle = 'Editar Usuario';
                $currentRoute = 'usuarios/edit'; // No es una ruta directa del menú, pero útil internamente
                $contentView = __DIR__ . '/../views/usuarios/edit.php';
                require_once __DIR__ . '/../views/layouts/master.php';
                return; // Importante: termina aquí para mostrar la vista de edición
            } else {
                $_SESSION['error'] = "Usuario no encontrado.";
            }
        } else {
            $_SESSION['error'] = "ID de usuario no especificado.";
        }
        // Si algo falla (no hay ID, usuario no encontrado), redirige al listado
        header("Location: index.php?route=usuarios_index");
        exit;
    }

    /**
     * Procesa los datos del formulario de edición y actualiza el usuario.
     */
    public function update() {
        session_start();
        if(!isset($_SESSION['user'])){
            header("Location: index.php?route=login");
            exit;
        }
        // Solo superusuarios pueden actualizar
         if ($_SESSION['user']['rol'] !== 'superusuario') {
             $_SESSION['error'] = "No tienes permisos para actualizar usuarios.";
             header("Location: index.php?route=dashboard");
             exit;
         }


        if(isset($_POST['id_usuario']) && isset($_POST)) {
            $id = intval($_POST['id_usuario']);

             // Evitar que un superusuario se edite a sí mismo si hay reglas especiales
            // if ($id === $_SESSION['user']['id_usuario']) {
            //    $_SESSION['error'] = "No puedes editar tu propio usuario desde aquí.";
            //    header("Location: index.php?route=usuarios_index");
            //    exit;
            // }

            // Validaciones del lado del servidor (similares a store, pero adaptadas)
            $nombre = trim($_POST['nombre']);
            $apellido_paterno = trim($_POST['apellido_paterno']);
            $apellido_materno = trim($_POST['apellido_materno']);
            $email = trim($_POST['email']);
            $username = trim($_POST['username']);
            $password = $_POST['password']; // Obtenerla, puede estar vacía
            $rol = trim($_POST['rol']);
            $estado = trim($_POST['estado']);

            // --- Realizar Validaciones ---
             if (empty($nombre) || empty($apellido_paterno) || empty($apellido_materno) || empty($email) || empty($username) || empty($rol) || empty($estado)) {
                 $_SESSION['error'] = "Los campos nombre, apellidos, email, username, rol y estado son obligatorios.";
                 header("Location: index.php?route=usuarios/edit&id=" . $id);
                 exit;
             }

            // Validación de nombres
             if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre) ||
                 !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno) ||
                 !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) {
                 $_SESSION['error'] = "Los campos de nombre y apellidos solo deben contener letras y espacios.";
                  header("Location: index.php?route=usuarios/edit&id=" . $id);
                 exit;
             }
            // Validación de email
             if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                 $_SESSION['error'] = "El email no tiene un formato válido.";
                  header("Location: index.php?route=usuarios/edit&id=" . $id);
                 exit;
             }
             // Validación de username
              if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
                  $_SESSION['error'] = "El nombre de usuario debe tener entre 4 y 20 caracteres alfanuméricos o guión bajo (_).";
                   header("Location: index.php?route=usuarios/edit&id=" . $id);
                  exit;
              }
            // Validación de contraseña (SOLO si se proporcionó una nueva)
            if (!empty($password)) {
                 if (strlen($password) < 6) {
                     $_SESSION['error'] = "La nueva contraseña debe tener al menos 6 caracteres.";
                      header("Location: index.php?route=usuarios/edit&id=" . $id);
                     exit;
                 }
            }
             // Validación de rol
             $roles_permitidos = ['superusuario', 'usuario'];
             if (!in_array($rol, $roles_permitidos)) {
                 $_SESSION['error'] = "El rol seleccionado no es válido.";
                  header("Location: index.php?route=usuarios/edit&id=" . $id);
                 exit;
             }
             // Validación de estado
             $estados_permitidos = ['activo', 'inactivo'];
             if (!in_array($estado, $estados_permitidos)) {
                 $_SESSION['error'] = "El estado seleccionado no es válido.";
                  header("Location: index.php?route=usuarios/edit&id=" . $id);
                 exit;
             }
            // --- Fin Validaciones ---


            $data = [
                'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'email' => $email,
                'username' => $username,
                 // Incluir la contraseña solo si no está vacía
                 // El modelo se encargará de hashearla si existe
                'password' => $password,
                'rol' => $rol,
                'estado' => $estado
            ];
             // Limpiar el array de contraseña si está vacía para que el modelo no la actualice
             if (empty($data['password'])) {
                 unset($data['password']);
             }

            if(User::update($id, $data)) {
                $_SESSION['message'] = "Usuario actualizado exitosamente.";
            } else {
                 $_SESSION['error'] = "Error al actualizar el usuario. Es posible que el email o username ya existan para otro usuario.";
                  // Redirigir de nuevo al formulario de edición en caso de error
                 header("Location: index.php?route=usuarios/edit&id=" . $id);
                 exit;
            }
        } else {
            $_SESSION['error'] = "Datos no válidos o ID de usuario no proporcionado.";
        }
         // Redirigir al listado después de la operación
        header("Location: index.php?route=usuarios_index");
        exit;
    }

    /**
     * Elimina un usuario existente.
     */
    public function delete() {
        session_start();
        if(!isset($_SESSION['user'])){
            header("Location: index.php?route=login");
            exit;
        }
        // Solo superusuarios pueden eliminar
        if ($_SESSION['user']['rol'] !== 'superusuario') {
             $_SESSION['error'] = "No tienes permisos para eliminar usuarios.";
             header("Location: index.php?route=dashboard");
             exit;
        }


        if(isset($_GET['id'])) {
            $id = intval($_GET['id']);

            // IMPORTANTE: Impedir que un usuario se elimine a sí mismo
            if ($id === $_SESSION['user']['id_usuario']) {
                $_SESSION['error'] = "No puedes eliminar tu propio usuario.";
                header("Location: index.php?route=usuarios_index");
                exit;
            }

            // Opcional: Añadir lógica para no poder eliminar al único superusuario, etc.


            if(User::delete($id)) {
                $_SESSION['message'] = "Usuario eliminado exitosamente.";
            } else {
                // El modelo debería loguear el error
                $_SESSION['error'] = "Error al eliminar el usuario. Puede estar asociado a otros registros.";
            }
        } else {
            $_SESSION['error'] = "ID de usuario no especificado.";
        }
        header("Location: index.php?route=usuarios_index");
        exit;
    }
}
?>