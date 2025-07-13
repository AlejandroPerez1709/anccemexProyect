<?php
// app/models/Documento.php
require_once __DIR__ . '/../../config/config.php';
// Constante UPLOADS_BASE_DIR definida en config.php

class Documento {

    /**
     * Maneja la subida de un archivo al servidor.
     * @return array ['status'=>..., 'data' => ...] o ['status'=>'error', 'message'=>...]
     */
    public static function handleUpload($fileInputName, $allowedTypes = [], $maxFileSize = 5000000, $subfolder = '') {
         // ... (Código de handleUpload sin cambios lógicos) ...
         if (!isset($_FILES[$fileInputName]) || empty($_FILES[$fileInputName]['tmp_name'])) { if (isset($_FILES[$fileInputName]['error']) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) { return ['status' => 'no_file', 'message' => 'No se seleccionó archivo.']; } return ['status' => 'no_file', 'message' => 'No se ha seleccionado ningún archivo o error en la subida inicial.']; } if ($_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) { $phpUploadErrors = [ UPLOAD_ERR_INI_SIZE => 'Excede tamaño servidor.', UPLOAD_ERR_FORM_SIZE => 'Excede tamaño formulario.', UPLOAD_ERR_PARTIAL => 'Subido parcialmente.', UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal.', UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir.', UPLOAD_ERR_EXTENSION => 'Extensión PHP detuvo subida.', ]; $errorCode = $_FILES[$fileInputName]['error']; $message = $phpUploadErrors[$errorCode] ?? 'Error desconocido (' . $errorCode . ').'; return ['status' => 'error', 'message' => $message]; } if (!is_uploaded_file($_FILES[$fileInputName]['tmp_name'])) { return ['status' => 'error', 'message' => 'El archivo no parece ser una subida válida.']; } $file = $_FILES[$fileInputName]; $fileSize = $file['size']; $originalName = basename($file['name']); $tmpPath = $file['tmp_name']; if ($fileSize == 0) { return ['status' => 'error', 'message' => 'El archivo está vacío.']; } if ($fileSize > $maxFileSize) { return ['status' => 'error', 'message' => 'Archivo demasiado grande. Máx: ' . round($maxFileSize / 1024 / 1024, 1) . ' MB.']; } if (!function_exists('finfo_open')) { error_log("Error: ext 'fileinfo' no habilitada."); return ['status' => 'error', 'message' => 'Error interno (finfo).']; } $fileInfo = finfo_open(FILEINFO_MIME_TYPE); $detectedMimeType = finfo_file($fileInfo, $tmpPath); finfo_close($fileInfo); if (!empty($allowedTypes) && !in_array($detectedMimeType, $allowedTypes)) { return ['status' => 'error', 'message' => 'Tipo de archivo no permitido (' . htmlspecialchars($detectedMimeType) . ').']; } $targetDir = UPLOADS_BASE_DIR; if (!empty($subfolder)) { $subfolder = trim(preg_replace('/[^a-zA-Z0-9_\-\/\\\\]+/', '', $subfolder), DIRECTORY_SEPARATOR); $targetDir .= $subfolder . DIRECTORY_SEPARATOR; } if (!is_dir($targetDir)) { if (!mkdir($targetDir, 0775, true)) { error_log("Error: No se pudo crear dir: " . $targetDir); return ['status' => 'error', 'message' => 'Error interno al crear dir.']; } } if (!is_writable($targetDir)) { error_log("Error: Dir no escribible: " . $targetDir); return ['status' => 'error', 'message' => 'Error interno de permisos.']; } $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)); try { $uniqueName = bin2hex(random_bytes(16)) . '.' . $fileExtension; } catch (Exception $e) { $uniqueName = uniqid('file_', true) . '.' . $fileExtension; } $safeFilename = $uniqueName; $destinationPath = $targetDir . $safeFilename; if (move_uploaded_file($tmpPath, $destinationPath)) { $relativePath = (!empty($subfolder) ? $subfolder . DIRECTORY_SEPARATOR : '') . $safeFilename; return ['status' => 'success', 'data' => ['originalName' => $originalName, 'savedPath' => $relativePath, 'mimeType' => $detectedMimeType, 'size' => $fileSize ]]; } else { error_log("Error: No se pudo mover archivo a: " . $destinationPath); return ['status' => 'error', 'message' => 'Error interno al guardar el archivo subido.']; }
    }


    /**
     * Guarda la información de un documento (VALIDADO POR DEFECTO).
     * @param array $data Datos del documento.
     * @return int|false Retorna el ID del documento insertado o false en caso de error.
     */
    public static function store($data) {
        $conn = dbConnect(); if (!$conn) { return false; }

        // Cambiado FALSE por TRUE para 'validado'
        $sql = "INSERT INTO documentos (
                    tipoDocumento, nombreArchivoOriginal, rutaArchivo, mimeType, sizeBytes,
                    socio_id, ejemplar_id, servicio_id,
                    id_usuario, comentarios, validado, fechaSubida
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE, NOW())"; // validado=TRUE por defecto

        $stmt = $conn->prepare($sql);
        if (!$stmt) { error_log("Prepare failed (doc store): " . $conn->error); $_SESSION['error_details'] = 'Error interno.'; $conn->close(); return false; }

        $tipoDocumento = $data['tipoDocumento']; $nombreOriginal = $data['nombreArchivoOriginal']; $rutaArchivo = $data['rutaArchivo']; $mimeType = $data['mimeType'] ?? null; $sizeBytes = $data['sizeBytes'] ?? null; $socio_id = !empty($data['socio_id']) ? filter_var($data['socio_id'], FILTER_VALIDATE_INT) : null; $ejemplar_id = !empty($data['ejemplar_id']) ? filter_var($data['ejemplar_id'], FILTER_VALIDATE_INT) : null; $servicio_id = !empty($data['servicio_id']) ? filter_var($data['servicio_id'], FILTER_VALIDATE_INT) : null; $id_usuario = $data['id_usuario']; $comentarios = $data['comentarios'] ?: null;
        if ($socio_id === null && $ejemplar_id === null && $servicio_id === null) { error_log("Error: Doc sin asociación."); $_SESSION['error_details'] = "Error asociación doc."; if ($stmt) $stmt->close(); $conn->close(); return false; }

        $types = "ssssiiiiis"; // 10 tipos (sin validado ni fecha)
        $stmt->bind_param($types,
            $tipoDocumento, $nombreOriginal, $rutaArchivo, $mimeType, $sizeBytes,
            $socio_id, $ejemplar_id, $servicio_id, $id_usuario, $comentarios
        );

        $result = false; $newId = false;
        try {
            $result = $stmt->execute();
            if ($result) { $newId = $conn->insert_id; if ($newId == 0) { $result=false; $_SESSION['error_details'] = 'Error ID.'; } }
            else { error_log("Execute failed (doc store): " . $stmt->error); $_SESSION['error_details'] = "Error BD."; $result=false; }
        } catch (mysqli_sql_exception $e) { error_log("Exception (doc store): " . $e->getMessage()); $_SESSION['error_details'] = 'Error DB.'; $result = false; }

        $success = (bool) $result;
        if ($stmt) $stmt->close();
        $conn->close();
        return ($success && $newId) ? $newId : false; // Retorna ID o false
    }

    /**
     * Obtiene un documento por su ID.
     * @param int $id ID del documento.
     * @return array|null Retorna un array con los datos o null si no se encuentra/error.
     */
    public static function getById($id) {
        $conn = dbConnect(); if (!$conn) { return null; }
        // Seleccionamos 'validado' y 'comentarios' aunque no los gestionemos activamente, por si se usan para mostrar info.
        $stmt = $conn->prepare("SELECT d.*, u.username as uploaded_by_username
                                FROM documentos d
                                LEFT JOIN usuarios u ON d.id_usuario = u.id_usuario
                                WHERE d.id_documento = ?");
        if (!$stmt) { error_log("Prepare failed (doc getById): ".$conn->error); $conn->close(); return null; }
        $stmt->bind_param("i", $id);
        $executeResult = $stmt->execute(); $documento = null;
        if($executeResult){ $result = $stmt->get_result(); $documento = ($result && $result->num_rows === 1) ? $result->fetch_assoc() : null; if($result) $result->free(); }
        else { error_log("Execute failed (doc getById): ".$stmt->error); }
        if($stmt) $stmt->close(); $conn->close(); return $documento;
    }


    /**
     * Obtiene documentos asociados a un ID específico.
     * @param string $entityType 'socio', 'ejemplar', o 'servicio'.
     * @param int $entityId ID de la entidad.
     * @param bool $onlyMasters Si es true, solo busca docs maestros (servicio_id IS NULL).
     * @return array Retorna un array de documentos (puede estar vacío).
     */
    public static function getByEntityId($entityType, $entityId, $onlyMasters = false) {
         $conn = dbConnect(); $documentos = []; if (!$conn) { return $documentos; }
         $columnName = ''; switch ($entityType) { case 'socio': $columnName = 'socio_id'; break; case 'ejemplar': $columnName = 'ejemplar_id'; break; case 'servicio': $columnName = 'servicio_id'; break; default: error_log("Tipo inválido: $entityType"); $conn->close(); return $documentos; }
         // Seleccionamos 'validado' y 'comentarios' por si se muestran en la vista.
         $sql = "SELECT d.*, u.username as uploaded_by_username FROM documentos d LEFT JOIN usuarios u ON d.id_usuario = u.id_usuario WHERE d.$columnName = ?";
         if ($onlyMasters && $entityType !== 'servicio') { $sql .= " AND d.servicio_id IS NULL"; }
         $sql .= " ORDER BY d.tipoDocumento, d.fechaSubida DESC";
         $stmt = $conn->prepare($sql);
         if (!$stmt) { error_log("Prepare failed (doc getByEntityId): ".$conn->error); $conn->close(); return $documentos; }
         $entityIdValidated = filter_var($entityId, FILTER_VALIDATE_INT); if (!$entityIdValidated) { if($stmt) $stmt->close(); $conn->close(); return $documentos; }
         $stmt->bind_param("i", $entityIdValidated); $executeResult = $stmt->execute();
         if ($executeResult) { $result = $stmt->get_result(); if ($result) { while ($row = $result->fetch_assoc()) { $documentos[] = $row; } $result->free(); } else { error_log("Error get_result (doc getByEntityId): " . $stmt->error); } }
         else { error_log("Error execute (doc getByEntityId): " . $stmt->error); }
         if($stmt) $stmt->close(); $conn->close();
         return $documentos; // Retorno explícito final array
    }


    /**
     * MÉTODO ELIMINADO - Ya no se necesita.
     * Actualiza el estado de validación y comentarios de un documento.
     */
    // public static function updateValidationStatus($id, $isValidated, $comentarios, $userId) { ... }


    /**
     * Elimina un registro de documento de la BD y su archivo físico.
     * @return bool
     */
    public static function delete($id) {
        // ... (Código de delete sin cambios, ya aseguraba retorno) ...
         $conn = dbConnect(); if (!$conn) return false; $conn->begin_transaction(); $result = false; try { $stmt_get = $conn->prepare("SELECT rutaArchivo FROM documentos WHERE id_documento = ?"); if (!$stmt_get) throw new Exception("Error prepare get: " . $conn->error); $stmt_get->bind_param("i", $id); $stmt_get->execute(); $result_get = $stmt_get->get_result(); $documento = ($result_get && $result_get->num_rows === 1) ? $result_get->fetch_assoc() : null; $stmt_get->close(); if (!$documento) throw new Exception("Doc ID $id no encontrado."); $rutaArchivoRelativa = $documento['rutaArchivo']; $stmt_del = $conn->prepare("DELETE FROM documentos WHERE id_documento = ?"); if (!$stmt_del) throw new Exception("Error prepare delete: " . $conn->error); $stmt_del->bind_param("i", $id); $result_db = $stmt_del->execute(); if (!$result_db) throw new Exception("Error execute delete DB: " . $stmt_del->error); $stmt_del->close(); $fullPath = realpath(UPLOADS_BASE_DIR . $rutaArchivoRelativa); if ($fullPath && strpos($fullPath, realpath(UPLOADS_BASE_DIR)) === 0 && is_file($fullPath)) { if (!unlink($fullPath)) { error_log("Error al eliminar archivo físico: $fullPath"); } else { error_log("Archivo físico eliminado: $fullPath"); } } else { error_log("Archivo físico no encontrado/inválido: $rutaArchivoRelativa"); } $conn->commit(); $result = true; } catch (Exception $e) { $conn->rollback(); error_log("Error transacción delete doc ID $id: " . $e->getMessage()); $_SESSION['error_details'] = "Error interno al borrar."; $result = false; } finally { if ($conn) $conn->close(); } return $result;
    }

} // Fin clase Documento
?>