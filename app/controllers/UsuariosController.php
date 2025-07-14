<?php
// app/controllers/UsuariosController.php
require_once __DIR__ . '/../models/User.php'; // Asegúrate que la ruta al modelo User es correcta
// Se incluye config.php para usar la función global check_permission()
require_once __DIR__ . '/../../config/config.php';

class UsuariosController {

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function create() {
        // CORREGIDO: Se usa la función global check_permission() para verificar permisos de superusuario.
        check_permission('superusuario'); 
        
        // Recuperar datos del formulario si hubo un error de validación previo para repoblar
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']); // Limpiar después de recuperarlos

        $pageTitle = 'Registrar Nuevo Usuario';
        $currentRoute = 'usuarios/create'; // Para marcar el menú activo
        $contentView = __DIR__ . '/../views/usuarios/create.php';
        // Pasar $formData a la vista
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Procesa los datos del formulario de creación y guarda el nuevo usuario.
     */
    public function store() {
        // CORREGIDO: Se usa la función global check_permission() para verificar permisos de superusuario.
        check_permission('superusuario');
        
        // Guardar todos los datos POST en sesión al inicio para repoblar el formulario si hay errores
        $_SESSION['form_data'] = $_POST;

        if(isset($_POST)) {
            // Recoger y limpiar datos
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
            $apellido_materno = trim($_POST['apellido_materno'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? ''; // No trimear contraseñas
            $rol = trim($_POST['rol'] ?? '');
            $estado = trim($_POST['estado'] ?? '');

            // --- Validaciones del Lado del Servidor (MEJORADAS) ---
            $errors = [];

            // 1. Validaciones de campos obligatorios
            if (empty($nombre)) $errors[] = "El nombre es obligatorio.";
            if (empty($apellido_paterno)) $errors[] = "El apellido paterno es obligatorio.";
            if (empty($apellido_materno)) $errors[] = "El apellido materno es obligatorio.";
            if (empty($email)) $errors[] = "El email es obligatorio.";
            if (empty($username)) $errors[] = "El nombre de usuario es obligatorio.";
            if (empty($password)) $errors[] = "La contraseña es obligatoria.";
            if (empty($rol)) $errors[] = "El rol es obligatorio.";
            if (empty($estado)) $errors[] = "El estado es obligatorio.";

            // 2. Validación de formato de nombres y apellidos (solo letras y espacios)
            if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre)) $errors[] = "El nombre contiene caracteres inválidos.";
            if (!empty($apellido_paterno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno)) $errors[] = "El apellido paterno contiene caracteres inválidos.";
            if (!empty($apellido_materno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) $errors[] = "El apellido materno contiene caracteres inválidos.";
            
            // 3. Validación de email (formato válido)
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El formato del email es inválido.";
            }

            // 4. Validación de username (alfanumérico, longitud 4-20)
             if (!empty($username) && !preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
                 $errors[] = "El nombre de usuario debe tener entre 4 y 20 caracteres alfanuméricos o guión bajo (_).";
             }

            // 5. Validación de contraseña (longitud mínima y complejidad opcional)
            if (!empty($password) && strlen($password) < 8) { // Mínimo 8 caracteres para ser más robusta
                 $errors[] = "La contraseña debe tener al menos 8 caracteres.";
                 // Opcional: añadir más reglas de complejidad (ej. al menos una mayúscula, un número, un símbolo)
                 // if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
                 //     $errors[] = "La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas, números y símbolos.";
                 // }
            }

            // 6. Validación de rol (solo valores permitidos)
            $roles_permitidos = ['superusuario', 'usuario'];
            if (!empty($rol) && !in_array($rol, $roles_permitidos)) {
                $errors[] = "El rol seleccionado no es válido.";
            }

            // 7. Validación de estado (solo valores permitidos)
            $estados_permitidos = ['activo', 'inactivo'];
            if (!empty($estado) && !in_array($estado, $estados_permitidos)) {
                $errors[] = "El estado seleccionado no es válido.";
            }

            // Si hay errores, guardarlos en sesión y redirigir
            if (!empty($errors)) {
                $_SESSION['error'] = "Se encontraron los siguientes problemas:<br>" . implode("<br>", $errors);
                header("Location: index.php?route=usuarios/create");
                exit;
            }

            // Preparar datos para guardar
            $data = [
                'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'email' => $email,
                'username' => $username,
                'password' => $password, // Se hashea en el modelo User.php
                'rol' => $rol,
                'estado' => $estado
            ];
            
            // Intentar guardar el usuario
            if(User::store($data)) {
                $_SESSION['message'] = "Usuario creado exitosamente.";
                unset($_SESSION['form_data']); // Limpiar datos del formulario en sesión
            } else {
                // El modelo User::store ya intenta establecer 'error_details' para duplicados
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al crear el usuario. Es posible que el email o nombre de usuario ya existan.';
                unset($_SESSION['error_details']); // Limpiar los detalles específicos del error del modelo
                $_SESSION['error'] = "Error al crear el usuario: " . $error_detail;
                // Mantener $_SESSION['form_data'] para que el formulario se repueble
                header("Location: index.php?route=usuarios/create"); 
                exit; 
            }
        } else { 
            $_SESSION['error'] = "No se recibieron datos del formulario.";
            header("Location: index.php?route=usuarios/create"); 
            exit; 
        }
        
        // Redirigir al listado si todo fue bien
        header("Location: index.php?route=usuarios_index");
        exit;
    }

    /**
     * Muestra la lista de todos los usuarios.
     */
    public function index() {
        // CORREGIDO: Se usa la función global check_permission() para verificar permisos de superusuario.
        check_permission('superusuario'); 
        
        $usuarios = User::getAll(); // Obtiene todos los usuarios desde el modelo
        $pageTitle = 'Listado de Usuarios';
        $currentRoute = 'usuarios_index'; // Para marcar el menú activo
        $contentView = __DIR__ . '/../views/usuarios/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Muestra el formulario para editar un usuario existente.
     */
    public function edit($id = null) {
        // CORREGIDO: Se usa la función global check_permission() para verificar permisos de superusuario.
        check_permission('superusuario');
        
        // Validar que el ID no sea el del propio usuario logueado (para evitar que se bloquee a sí mismo)
        if (isset($_SESSION['user']['id_usuario']) && $id == $_SESSION['user']['id_usuario']) {
            $_SESSION['error'] = "No puedes editar tu propio usuario desde esta interfaz por seguridad. Utiliza la opción de perfil (si existiera) o pide a otro superusuario que lo haga.";
            header("Location: index.php?route=usuarios_index");
            exit;
        }

        $usuarioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($usuarioId) {
            $usuario = User::getById($usuarioId); // Obtiene el usuario por ID desde el modelo

            if($usuario) {
                // Si se cargan datos de un usuario, usarlos para repoblar el formulario
                // Si hubo un error previo en update(), $_SESSION['form_data'] tendrá prioridad
                $formData = $_SESSION['form_data'] ?? $usuario; // Repoblar con datos del usuario o de la sesión si hubo error
                unset($_SESSION['form_data']); // Limpiar después de usarlos

                $pageTitle = 'Editar Usuario';
                $currentRoute = 'usuarios/edit'; // No es una ruta directa del menú, pero útil internamente
                $contentView = __DIR__ . '/../views/usuarios/edit.php';
                // Pasar $formData a la vista
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
        // CORREGIDO: Se usa la función global check_permission() para verificar permisos de superusuario.
        check_permission('superusuario');
        
        // Recuperar el ID al inicio para poder redirigir a la misma página de edición en caso de error
        $id = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de usuario inválido."; 
            header("Location: index.php?route=usuarios_index"); 
            exit;
        }

        // Validar que el ID no sea el del propio usuario logueado (por seguridad)
        if (isset($_SESSION['user']['id_usuario']) && $id == $_SESSION['user']['id_usuario']) {
            $_SESSION['error'] = "No puedes actualizar tu propio usuario desde esta interfaz por seguridad.";
            header("Location: index.php?route=usuarios_index");
            exit;
        }
        
        // Guardar todos los datos POST en sesión para repoblar el formulario si hay errores
        $_SESSION['form_data'] = $_POST; 

        if(isset($_POST)) {
            // Recoger y limpiar datos
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
            $apellido_materno = trim($_POST['apellido_materno'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? ''; // No trimear contraseñas, puede venir vacía
            $rol = trim($_POST['rol'] ?? '');
            $estado = trim($_POST['estado'] ?? '');

            // --- Validaciones del Lado del Servidor (similar a store) ---
            $errors = [];

            // 1. Validaciones de campos obligatorios
             if (empty($nombre)) $errors[] = "El nombre es obligatorio.";
             if (empty($apellido_paterno)) $errors[] = "El apellido paterno es obligatorio.";
             if (empty($apellido_materno)) $errors[] = "El apellido materno es obligatorio.";
             if (empty($email)) $errors[] = "El email es obligatorio.";
             if (empty($username)) $errors[] = "El nombre de usuario es obligatorio.";
             if (empty($rol)) $errors[] = "El rol es obligatorio.";
             if (empty($estado)) $errors[] = "El estado es obligatorio.";

            // 2. Validación de formato de nombres y apellidos
             if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre)) $errors[] = "El nombre contiene caracteres inválidos.";
             if (!empty($apellido_paterno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno)) $errors[] = "El apellido paterno contiene caracteres inválidos.";
             if (!empty($apellido_materno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) $errors[] = "El apellido materno contiene caracteres inválidos.";
            
            // 3. Validación de email
             if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                 $errors[] = "El formato del email es inválido.";
             }
             
            // 4. Validación de username
              if (!empty($username) && !preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
                  $errors[] = "El nombre de usuario debe tener entre 4 y 20 caracteres alfanuméricos o guión bajo (_).";
              }
            
            // 5. Validación de contraseña (SOLO si se proporcionó una nueva)
            if (!empty($password)) {
                 if (strlen($password) < 8) { // Mínimo 8 caracteres
                     $errors[] = "La nueva contraseña debe tener al menos 8 caracteres.";
                     // Opcional: más reglas de complejidad
                 }
            }
            
             // 6. Validación de rol
             $roles_permitidos = ['superusuario', 'usuario'];
             if (!empty($rol) && !in_array($rol, $roles_permitidos)) {
                 $errors[] = "El rol seleccionado no es válido.";
             }
            
             // 7. Validación de estado
             $estados_permitidos = ['activo', 'inactivo'];
             if (!empty($estado) && !in_array($estado, $estados_permitidos)) {
                 $errors[] = "El estado seleccionado no es válido.";
             }

            // Si hay errores, guardarlos en sesión y redirigir
            if (!empty($errors)) {
                $_SESSION['error'] = "Se encontraron los siguientes problemas:<br>" . implode("<br>", $errors);
                header("Location: index.php?route=usuarios/edit&id=" . $id); 
                exit;
            }

            // Preparar datos para actualizar
            $data = [
                'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'email' => $email,
                'username' => $username,
                // Incluir la contraseña solo si no está vacía (el modelo se encarga de hashearla)
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
                unset($_SESSION['form_data']); // Limpiar datos del formulario en sesión
            } else {
                // El modelo User::update ya intenta establecer 'error_details' para duplicados
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al actualizar el usuario. Es posible que el email o nombre de usuario ya existan para otro usuario.';
                unset($_SESSION['error_details']); // Limpiar los detalles específicos del error del modelo
                $_SESSION['error'] = "Error al actualizar el usuario: " . $error_detail;
                // Mantener $_SESSION['form_data'] para que el formulario se repueble
                header("Location: index.php?route=usuarios/edit&id=" . $id); 
                exit;
            }
        } else { 
            $_SESSION['error'] = "No se recibieron datos del formulario.";
            header("Location: index.php?route=usuarios/edit&id=" . $id); 
            exit; 
        }
        
        // Redirigir al listado después de la operación
        header("Location: index.php?route=usuarios_index");
        exit;
    }

    /**
     * Elimina un usuario existente.
     */
    public function delete($id = null) {
        // CORREGIDO: Se usa la función global check_permission() para verificar permisos de superusuario.
        check_permission('superusuario'); 

        // Validar que el ID no sea el del propio usuario logueado (por seguridad)
        $usuarioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (isset($_SESSION['user']['id_usuario']) && $usuarioId == $_SESSION['user']['id_usuario']) {
            $_SESSION['error'] = "No puedes eliminar tu propio usuario.";
            header("Location: index.php?route=usuarios_index");
            exit;
        }

        if(isset($_GET['id'])) {
            $id = intval($_GET['id']);
            // Opcional: Añadir lógica para no poder eliminar al único superusuario, etc.

            if(User::delete($id)) {
                $_SESSION['message'] = "Usuario eliminado exitosamente.";
            } else {
                // El modelo debería loguear el error y establecer $_SESSION['error_details']
                $_SESSION['error'] = "Error al eliminar el usuario. " . ($_SESSION['error_details'] ?? 'Puede estar asociado a otros registros o no exista.');
                unset($_SESSION['error_details']);
            }
        } else {
            $_SESSION['error'] = "ID de usuario no especificado.";
        }
        header("Location: index.php?route=usuarios_index");
        exit;
    }
}