<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Constantes de Base de Datos ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'anccemex_db');

// --- Constantes de la Aplicación ---
define('APP_NAME', 'Sistema ANCCEMEX');
define('BASE_URL', 'http://localhost/anccemexProyecto/public');

// --- ZONA HORARIA ---
date_default_timezone_set('America/Mexico_City');

// --- RUTA BASE PARA SUBIDA DE ARCHIVOS ---
define('UPLOADS_BASE_DIR', realpath(__DIR__ . '/../uploads') . DIRECTORY_SEPARATOR);

if (!defined('UPLOADS_BASE_DIR') || !is_dir(UPLOADS_BASE_DIR)) {
     error_log("ERROR CRITICO: UPLOADS_BASE_DIR no definido o no existe.");
} elseif (!is_writable(UPLOADS_BASE_DIR)) {
      error_log("ERROR CRITICO: UPLOADS_BASE_DIR no tiene permisos de escritura.");
}

/**
 * Establece una conexión con la base de datos.
 */
function dbConnect(){
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if($conn->connect_error){
        error_log("Conexión fallida a BD: " . $conn->connect_error);
        return false;
    }
     if (!$conn->set_charset("utf8mb4")) {
         error_log("Error cargando el conjunto de caracteres utf8mb4: %s\n", $conn->error);
     }
    return $conn;
}

// ****** FUNCIONES AUXILIARES DE PERMISOS ******

function check_permission($required_role = 'usuario') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user'])) {
        $_SESSION['error'] = "Acceso denegado. Debes iniciar sesión.";
        // ***** CORREGIDO: Se usa BASE_URL para una redirección absoluta y segura *****
        header("Location: " . BASE_URL . "/index.php?route=login");
        exit;
    }

    $user_role = $_SESSION['user']['rol'] ?? 'invitado';
    $hasPermission = false;

    if ($required_role === 'superusuario') {
        if ($user_role === 'superusuario') {
            $hasPermission = true;
        }
    } elseif ($required_role === 'usuario') {
        if ($user_role === 'usuario' || $user_role === 'superusuario') {
            $hasPermission = true;
        }
    }

    if (!$hasPermission) {
        $_SESSION['error'] = "Acceso denegado. Permisos insuficientes para realizar esta acción.";
        // ***** CORREGIDO: Se usa BASE_URL para una redirección absoluta y segura *****
        header("Location: " . BASE_URL . "/index.php?route=dashboard");
        exit;
    }
}

function is_admin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return (isset($_SESSION['user']['rol']) && $_SESSION['user']['rol'] === 'superusuario');
}