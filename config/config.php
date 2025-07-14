<?php
// config/config.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Constantes de Base de Datos ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Ajusta según tu configuración
define('DB_NAME', 'anccemex_db');

// --- Constantes de la Aplicación ---
define('APP_NAME', 'Sistema ANCCEMEX');
// Asegúrate que esta URL base sea correcta para tu entorno local
define('BASE_URL', 'http://localhost/anccemexProyecto/public');

// --- ZONA HORARIA ---
date_default_timezone_set('America/Mexico_City');

// --- RUTA BASE PARA SUBIDA DE ARCHIVOS ---
// __DIR__ aquí es la carpeta 'config'. Subimos un nivel ('../') y entramos a 'uploads'.
define('UPLOADS_BASE_DIR', realpath(__DIR__ . '/../uploads') . DIRECTORY_SEPARATOR);
// Verificar que el directorio exista y sea escribible
if (!defined('UPLOADS_BASE_DIR') || !is_dir(UPLOADS_BASE_DIR)) {
     error_log("ERROR CRITICO: UPLOADS_BASE_DIR no definido o no existe: " . (defined('UPLOADS_BASE_DIR') ? UPLOADS_BASE_DIR : 'No definida'));
     // Podrías mostrar un error más visible aquí si es necesario
     // die("Error config: Directorio uploads inválido.");
} elseif (!is_writable(UPLOADS_BASE_DIR)) {
      error_log("ERROR CRITICO: UPLOADS_BASE_DIR no tiene permisos de escritura: " . UPLOADS_BASE_DIR);
      // die("Error config: Directorio uploads sin permisos.");
}


/**
 * Establece una conexión con la base de datos.
 * @return mysqli|false Objeto mysqli en caso de éxito, false en caso de error.
 */
function dbConnect(){
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");
    if($conn->connect_error){
        error_log("Conexión fallida a BD: " . $conn->connect_error);
        return false;
    }
     if (!$conn->set_charset("utf8mb4")) {
         error_log("Error cargando el conjunto de caracteres utf8mb4: %s\n", $conn->error);
     }
    return $conn;
}


// ****** NUEVAS FUNCIONES AUXILIARES PARA PERMISOS ******

/**
 * Verifica si el usuario actual tiene el rol requerido para acceder a una acción.
 * Si no tiene permiso, establece un mensaje de error en la sesión y redirige.
 * El rol 'superusuario' tiene acceso a todo lo que requiere 'usuario'.
 *
 * @param string $required_role Rol mínimo requerido ('usuario' o 'superusuario'). Por defecto 'usuario'.
 * @return void No devuelve nada, pero llama a exit() si no hay permiso.
 */
function check_permission($required_role = 'usuario') {
    // Iniciar sesión si aún no está activa
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 1. Verificar si hay un usuario logueado
    if (!isset($_SESSION['user'])) {
        $_SESSION['error'] = "Acceso denegado. Debes iniciar sesión.";
        // Usamos BASE_URL para asegurar la redirección correcta desde cualquier parte
        header("Location: " . BASE_URL . "/../index.php?route=login"); // Asume que index.php está en public
        exit;
    }

    // 2. Obtener el rol del usuario actual
    $user_role = $_SESSION['user']['rol'] ?? 'invitado'; // Asignar 'invitado' si no hay rol definido

    // 3. Verificar permisos
    $hasPermission = false;
    if ($required_role === 'superusuario') {
        if ($user_role === 'superusuario') {
            $hasPermission = true;
        }
    } elseif ($required_role === 'usuario') {
        // Si se requiere rol 'usuario', tanto 'usuario' como 'superusuario' tienen permiso
        if ($user_role === 'usuario' || $user_role === 'superusuario') {
            $hasPermission = true;
        }
    } // Podríamos añadir más roles aquí en el futuro

    // 4. Si no tiene permiso, redirigir con error
    if (!$hasPermission) {
        $_SESSION['error'] = "Acceso denegado. Permisos insuficientes para realizar esta acción.";
        // Redirigir al dashboard como página segura por defecto tras login
        header("Location: " . BASE_URL . "/../index.php?route=dashboard");
        exit;
    }

    // Si llega aquí, tiene permiso y la ejecución continúa.
}

/**
 * Verifica si el usuario actual tiene el rol de 'superusuario'.
 * Útil para mostrar/ocultar elementos en las vistas.
 *
 * @return bool True si es superusuario, False en caso contrario.
 */
function is_admin() {
    // Iniciar sesión si aún no está activa
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Devuelve true solo si existe la sesión, el rol está definido y es 'superusuario'
    return (isset($_SESSION['user']['rol']) && $_SESSION['user']['rol'] === 'superusuario');
}

// ****** FIN NUEVAS FUNCIONES AUXILIARES ******

?>