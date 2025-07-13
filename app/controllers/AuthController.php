<?php
// app/controllers/AuthController.php
require_once __DIR__ . '/../models/User.php'; // Asegúrate que la ruta es correcta

class AuthController {

    /**
     * Muestra la vista del formulario de login.
     */
    public function login(){
        // Si ya hay una sesión activa, redirigir al dashboard directamente
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if(isset($_SESSION['user'])){
            header("Location: index.php?route=dashboard");
            exit;
        }
        // Cargar la vista de login
        require_once __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Procesa los datos del formulario de login, verifica credenciales y crea la sesión.
     */
    public function authenticate(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar si se enviaron datos POST
        if(isset($_POST['username']) && isset($_POST['password'])){
            $username = trim($_POST['username']);
            $password = $_POST['password']; // La contraseña no se trimea

            // Validar que los campos no estén vacíos después de trim (para username)
            if (empty($username) || empty($password)) {
                 $_SESSION['error'] = "Por favor, ingresa usuario y contraseña.";
                 header("Location: index.php?route=login");
                 exit;
            }


            // Buscar al usuario por username y que esté activo
            $user = User::getByUsername($username); // Este método ya busca usuarios activos

            if($user){
                
                // Verificar si la contraseña proporcionada coincide con el hash guardado en la BD
                if(password_verify($password, $user['password'])) {
                    // ¡Contraseña correcta! Iniciar sesión.
                    $_SESSION['user'] = $user; // Guardar todos los datos del usuario en la sesión

                    // Actualizar la fecha del último login
                    User::updateLastLogin($user['id_usuario']);

                    // Redirigir al dashboard
                    header("Location: index.php?route=dashboard");
                    exit;
                } else {
                    // Contraseña incorrecta
                    $_SESSION['error'] = "Credenciales incorrectas.";
                    // Opcional: Registrar intento fallido aquí si implementas esa tabla
                }
            } else {
                // Usuario no encontrado o inactivo
                $_SESSION['error'] = "Usuario no encontrado o inactivo.";
                 // Opcional: Registrar intento fallido
            }
        } else {
            // No se enviaron los datos esperados
            $_SESSION['error'] = "Por favor, ingresa usuario y contraseña.";
        }

        // Si la autenticación falla por cualquier motivo, redirigir de vuelta al login
        header("Location: index.php?route=login");
        exit;
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Destruir todas las variables de sesión.
        $_SESSION = array();

        // Si se desea destruir la sesión completamente, borra también la cookie de sesión.
        // Nota: ¡Esto destruirá la sesión, y no solo los datos de la sesión!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finalmente, destruir la sesión.
        session_destroy();

        // Redirigir a la página de login
        header("Location: index.php?route=login");
        exit;
    }
}
?>