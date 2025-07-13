<?php
// app/controllers/DocumentosController.php
require_once __DIR__ . '/../models/Documento.php';

class DocumentosController {

    // Helper para verificar sesión y rol (ej. solo usuarios logueados pueden descargar, admin puede borrar)
    private function checkAccess($requireAdmin = false) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Acceso denegado. Debes iniciar sesión.";
            header("Location: index.php?route=login");
            exit;
        }
        if ($requireAdmin && (!isset($_SESSION['user']['rol']) || $_SESSION['user']['rol'] !== 'superusuario')) {
             $_SESSION['error'] = "Acceso denegado. Permisos insuficientes.";
             header("Location: index.php?route=dashboard");
             exit;
        }
    }

    /**
     * Maneja la descarga segura de un archivo.
     * Espera el ID del documento como parámetro GET 'id'.
     */
    public function download($id = null) {
        // Permitir descarga a cualquier usuario logueado? O solo admin? Por ahora, logueado.
        $this->checkAccess(false);
        $docId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$docId) {
            http_response_code(400); echo "ID de documento inválido."; exit;
        }

        $documento = Documento::getById($docId);

        if (!$documento || empty($documento['rutaArchivo'])) {
             http_response_code(404); echo "Documento no encontrado o ruta inválida."; exit;
        }

        // Asegurarse que la constante UPLOADS_BASE_DIR está definida (debería estar en config.php)
        if (!defined('UPLOADS_BASE_DIR')) {
             error_log("CRITICAL: UPLOADS_BASE_DIR no definida en descarga.");
             http_response_code(500); echo "Error interno del servidor."; exit;
        }

        // Construir la ruta absoluta al archivo
        $fullPath = realpath(UPLOADS_BASE_DIR . $documento['rutaArchivo']);

        // --- Verificación de Seguridad Crucial ---
        if (!$fullPath || strpos($fullPath, realpath(UPLOADS_BASE_DIR)) !== 0 || !is_file($fullPath)) {
             error_log("Intento de acceso inválido o archivo no encontrado en descarga: ID=$docId, Path=" . $documento['rutaArchivo'] . ", Resolved=" . ($fullPath ?: 'Inválido'));
             http_response_code(404); echo "Archivo no disponible."; exit;
        }

        // Enviar cabeceras para forzar descarga
        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($documento['mimeType'] ?: 'application/octet-stream'));
        // Usar basename() en nombre original por si contiene caracteres extraños
        header('Content-Disposition: attachment; filename="' . basename($documento['nombreArchivoOriginal']) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));

        // Limpiar buffer de salida y enviar archivo
        ob_clean();
        flush();
        if (!readfile($fullPath)) {
             error_log("Error al leer archivo para descarga: " . $fullPath);
              // Puede que ya se hayan enviado cabeceras, así que solo logueamos
        }
        exit; // Terminar script después de enviar el archivo
    }

    /**
     * Maneja la eliminación de un documento (registro y archivo).
     * Espera el ID del documento como parámetro GET 'id'.
     * Espera opcionalmente IDs de entidad (socio_id, ejemplar_id, servicio_id) para redirigir.
     */
    public function delete($id = null) {
        $this->checkAccess(true); // Solo admin puede borrar
        $docId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        // Determinar a dónde redirigir después de borrar
        $redirectRoute = 'dashboard'; // Ruta por defecto
        $redirectParams = '';
         $redirectId = null; // Para el ID de la entidad

         if (!empty($_GET['socio_id'])) {
              $redirectRoute = 'socios/edit';
              $redirectId = filter_input(INPUT_GET, 'socio_id', FILTER_VALIDATE_INT);
         } elseif (!empty($_GET['ejemplar_id'])) {
              $redirectRoute = 'ejemplares/edit';
               $redirectId = filter_input(INPUT_GET, 'ejemplar_id', FILTER_VALIDATE_INT);
          } elseif (!empty($_GET['servicio_id'])) {
               $redirectRoute = 'servicios/edit';
               $redirectId = filter_input(INPUT_GET, 'servicio_id', FILTER_VALIDATE_INT);
           }
           // Construir parámetros de redirección si hay ID
           if ($redirectId) {
                $redirectParams = '&id=' . $redirectId;
           } else {
                // Si no se especificó entidad para volver, ir al dashboard o un índice general
                $redirectRoute = 'dashboard'; // O tal vez un futuro 'documentos_index'?
           }


        if (!$docId) {
            $_SESSION['error'] = "ID de documento inválido.";
        } else {
            if (Documento::delete($docId)) {
                 $_SESSION['message'] = "Documento ID:{$docId} eliminado correctamente.";
            } else {
                 $_SESSION['error'] = "No se pudo eliminar el documento ID:{$docId}. " . ($_SESSION['error_details'] ?? '');
                 unset($_SESSION['error_details']);
            }
        }
        // Redirigir a la ruta calculada
        header("Location: index.php?route=" . $redirectRoute . $redirectParams);
        exit;
    }

    // --- MÉTODO validateDoc() ELIMINADO ---

} // Fin clase DocumentosController
?>