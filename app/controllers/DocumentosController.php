<?php
// app/controllers/DocumentosController.php
require_once __DIR__ . '/../models/Documento.php';
class DocumentosController {

    /**
     * Maneja la descarga o visualización segura de un archivo.
     */
    public function download($id = null) {
        // Se requiere como mínimo un 'usuario' logueado.
        check_permission('usuario');
        
        $docId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$docId) {
            http_response_code(400);
            echo "ID de documento inválido.";
            exit;
        }

        $documento = Documento::getById($docId);
        if (!$documento || empty($documento['rutaArchivo'])) {
             http_response_code(404);
             echo "Documento no encontrado o ruta inválida."; exit;
        }

        if (!defined('UPLOADS_BASE_DIR')) {
             error_log("CRITICAL: UPLOADS_BASE_DIR no definida en descarga.");
             http_response_code(500); echo "Error interno del servidor."; exit;
        }

        $fullPath = realpath(UPLOADS_BASE_DIR . $documento['rutaArchivo']);
        if (!$fullPath || strpos($fullPath, realpath(UPLOADS_BASE_DIR)) !== 0 || !is_file($fullPath)) {
             error_log("Intento de acceso inválido o archivo no encontrado en descarga: ID=$docId, Path=" . $documento['rutaArchivo']);
             http_response_code(404); echo "Archivo no disponible."; exit;
        }

        // --- INICIO DE MODIFICACIÓN: Ver vs Descargar ---
        $mimeType = $documento['mimeType'] ?: 'application/octet-stream';
        $viewableTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        $disposition = in_array($mimeType, $viewableTypes) ? 'inline' : 'attachment';
        // --- FIN DE MODIFICACIÓN ---

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: ' . $disposition . '; filename="' . basename($documento['nombreArchivoOriginal']) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));
        
        ob_clean();
        flush();
        
        if (!readfile($fullPath)) {
             error_log("Error al leer archivo para descarga: " . $fullPath);
        }
        exit;
    }

    /**
     * Maneja la eliminación de un documento (registro y archivo).
     * MODIFICADO: Ahora puede responder a solicitudes AJAX con JSON.
     */
    public function delete($id = null) {
        check_permission('superusuario');
        $docId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        // Detectar si es una solicitud AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if (!$docId) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID de documento inválido.']);
                exit;
            }
            $_SESSION['error'] = "ID de documento inválido.";
        } else {
            if (Documento::delete($docId)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'success', 'message' => "Documento ID:{$docId} eliminado correctamente."]);
                    exit;
                }
                $_SESSION['message'] = "Documento ID:{$docId} eliminado correctamente.";
            } else {
                $errorMessage = "No se pudo eliminar el documento ID:{$docId}. " . ($_SESSION['error_details'] ?? '');
                unset($_SESSION['error_details']);
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => $errorMessage]);
                    exit;
                }
                $_SESSION['error'] = $errorMessage;
            }
        }
        
        // Esta parte solo se ejecutará para solicitudes no-AJAX (las tradicionales)
        $redirectRoute = 'dashboard';
        $redirectParams = '';
        $redirectId = null;
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
           
           if ($redirectId) {
                $redirectParams = '&id=' . $redirectId;
           } else {
                $redirectRoute = 'dashboard';
           }

        header("Location: index.php?route=" . $redirectRoute . $redirectParams);
        exit;
    }
}