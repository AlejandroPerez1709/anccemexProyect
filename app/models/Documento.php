<?php
// app/models/Documento.php
require_once __DIR__ . '/../../config/config.php';
// Constante UPLOADS_BASE_DIR definida en config.php

class Documento {

    /**
     * Maneja la subida de un archivo al servidor.
     * @param string $fileInputName El 'name' del input file del formulario.
     * @param array $allowedTypes Array de MIME types permitidos.
     * @param int $maxFileSize Tamaño máximo permitido en bytes.
     * @param string $subfolder Subcarpeta dentro de UPLOADS_BASE_DIR (ej. 'socios', 'ejemplares/ID/fotos').
     * @param array|null $filesArray Opcional: El array de archivos a procesar (por defecto usa $_FILES global).
     * @return array ['status'=>..., 'data' => ...] o ['status'=>'error', 'message'=>...]
     */
    public static function handleUpload($fileInputName, $allowedTypes = [], $maxFileSize = 5000000, $subfolder = '', $filesArray = null) {
        $sourceFiles = ($filesArray !== null) ? $filesArray : $_FILES;

        if (!isset($sourceFiles[$fileInputName]) || empty($sourceFiles[$fileInputName]['tmp_name'])) { 
            if (isset($sourceFiles[$fileInputName]['error']) && $sourceFiles[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) { 
                return ['status' => 'no_file', 'message' => 'No se seleccionó archivo.'];
            } 
            return ['status' => 'no_file', 'message' => 'No se ha seleccionado ningún archivo o error en la subida inicial.'];
        } 
        
        $file = $sourceFiles[$fileInputName];
        if ($file['error'] !== UPLOAD_ERR_OK) { 
            $phpUploadErrors = [ 
                UPLOAD_ERR_INI_SIZE => 'Excede tamaño servidor (php.ini).', 
                UPLOAD_ERR_FORM_SIZE => 'Excede tamaño formulario.', 
                UPLOAD_ERR_PARTIAL => 'Subido parcialmente.', 
                UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal.', 
                UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir en disco.', 
                UPLOAD_ERR_EXTENSION => 'Extensión PHP detuvo subida.', 
            ];
            $errorCode = $file['error']; 
            $message = $phpUploadErrors[$errorCode] ?? 'Error desconocido en la subida (' . $errorCode . ').';
            return ['status' => 'error', 'message' => $message]; 
        } 
        
        if (!is_uploaded_file($file['tmp_name'])) { 
            return ['status' => 'error', 'message' => 'El archivo no parece ser una subida válida.'];
        } 
        
        $fileSize = $file['size'];
        $originalName = basename($file['name']); 
        $tmpPath = $file['tmp_name'];

        if ($fileSize == 0) { 
            return ['status' => 'error', 'message' => 'El archivo está vacío.'];
        } 
        
        if ($fileSize > $maxFileSize) { 
            return ['status' => 'error', 'message' => 'Archivo demasiado grande. Máx: ' . round($maxFileSize / 1024 / 1024, 1) . ' MB.'];
        } 
        
        if (!function_exists('finfo_open')) { 
            error_log("Error: ext 'fileinfo' no habilitada. Necesaria para validar MIME type.");
            return ['status' => 'error', 'message' => 'Error interno del servidor (finfo no disponible).'];
        } 
        
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMimeType = finfo_file($fileInfo, $tmpPath); 
        finfo_close($fileInfo);

        if (!empty($allowedTypes) && !in_array($detectedMimeType, $allowedTypes)) { 
            return ['status' => 'error', 'message' => 'Tipo de archivo no permitido (' . htmlspecialchars($detectedMimeType) . ').'];
        } 
        
        $targetDir = UPLOADS_BASE_DIR;
        if (!empty($subfolder)) { 
            $subfolder = trim(preg_replace('/[^a-zA-Z0-9_\-\/\\\\]+/', '', $subfolder), DIRECTORY_SEPARATOR);
            $targetDir .= $subfolder . DIRECTORY_SEPARATOR;
        } 
        
        if (!is_dir($targetDir)) { 
            if (!mkdir($targetDir, 0775, true)) { 
                error_log("Error: No se pudo crear directorio de subida: " . $targetDir);
                return ['status' => 'error', 'message' => 'Error interno al crear directorio en el servidor.'];
            } 
        } 
        
        if (!is_writable($targetDir)) { 
            error_log("Error: Directorio de subida no escribible: " . $targetDir);
            return ['status' => 'error', 'message' => 'Error interno de permisos en el servidor.'];
        } 
        
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        try { 
            $uniqueName = bin2hex(random_bytes(16)) . '.' . $fileExtension; 
        } catch (Exception $e) { 
            $uniqueName = uniqid('file_', true) . '.' . $fileExtension;
        } 
        
        $safeFilename = $uniqueName;
        $destinationPath = $targetDir . $safeFilename;

        if (move_uploaded_file($tmpPath, $destinationPath)) { 
            $relativePath = (!empty($subfolder) ? $subfolder . DIRECTORY_SEPARATOR : '') . $safeFilename;
            return ['status' => 'success', 'data' => ['originalName' => $originalName, 'savedPath' => $relativePath, 'mimeType' => $detectedMimeType, 'size' => $fileSize ]];
        } else { 
            error_log("Error: No se pudo mover archivo subido a: " . $destinationPath);
            return ['status' => 'error', 'message' => 'Error interno al guardar el archivo subido en el servidor.'];
        }
    }

    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) { return false; }

        $sql = "INSERT INTO documentos (
                    tipoDocumento, nombreArchivoOriginal, rutaArchivo, mimeType, sizeBytes,
                    socio_id, ejemplar_id, servicio_id,
                    id_usuario, comentarios, validado, fechaSubida
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE, NOW())";

        $stmt = $conn->prepare($sql);
        if (!$stmt) { error_log("Prepare failed (doc store): " . $conn->error); $_SESSION['error_details'] = 'Error interno al preparar la inserción del documento.'; $conn->close(); return false; }

        $socio_id = !empty($data['socio_id']) ? filter_var($data['socio_id'], FILTER_VALIDATE_INT) : null;
        $ejemplar_id = !empty($data['ejemplar_id']) ? filter_var($data['ejemplar_id'], FILTER_VALIDATE_INT) : null; 
        $servicio_id = !empty($data['servicio_id']) ? filter_var($data['servicio_id'], FILTER_VALIDATE_INT) : null;
        if ($socio_id === null && $ejemplar_id === null && $servicio_id === null) { 
            error_log("Error: Intento de guardar documento sin asociación a entidad (socio, ejemplar, servicio).");
            $_SESSION['error_details'] = "Error: El documento debe estar asociado a un Socio, Ejemplar o Servicio."; 
            if ($stmt) $stmt->close(); 
            $conn->close(); 
            return false;
        }

        $stmt->bind_param("ssssiiiiis",
            $data['tipoDocumento'], $data['nombreArchivoOriginal'], $data['rutaArchivo'], $data['mimeType'], $data['sizeBytes'],
            $socio_id, $ejemplar_id, $servicio_id, $data['id_usuario'], $data['comentarios']
        );
        $newId = false;
        try {
            if ($stmt->execute()) { 
                $newId = $conn->insert_id;
            } else { 
                error_log("Execute failed (doc store): " . $stmt->error);
                $_SESSION['error_details'] = "Error de base de datos al guardar el documento: " . $stmt->error;
            }
        } catch (mysqli_sql_exception $e) { 
            error_log("Exception (doc store): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al guardar el documento (' . $e->getCode() . '): ' . $e->getMessage();
        }

        if ($stmt) $stmt->close();
        $conn->close();
        return $newId ?: false;
    }

    public static function getById($id) {
        $conn = dbConnect();
        if (!$conn) { return null; }
        
        $stmt = $conn->prepare("SELECT d.*, u.username as uploaded_by_username
                                FROM documentos d
                                LEFT JOIN usuarios u ON d.id_usuario = u.id_usuario
                                WHERE d.id_documento = ?");
        if (!$stmt) { error_log("Prepare failed (doc getById): ".$conn->error); $conn->close(); return null; }
        
        $stmt->bind_param("i", $id);
        $documento = null;
        if($stmt->execute()){ 
            $result = $stmt->get_result();
            $documento = ($result && $result->num_rows === 1) ? $result->fetch_assoc() : null; 
            if($result) $result->free();
        } else { 
            error_log("Execute failed (doc getById): ".$stmt->error);
        }
        if($stmt) $stmt->close(); 
        $conn->close(); 
        return $documento;
    }

    public static function getByEntityId($entityType, $entityId, $onlyMasters = false) {
         $conn = dbConnect();
         $documentos = []; 
         if (!$conn) { return $documentos; }
         
         $columnName = '';
         switch ($entityType) { 
             case 'socio': $columnName = 'socio_id'; break; 
             case 'ejemplar': $columnName = 'ejemplar_id'; break; 
             case 'servicio': $columnName = 'servicio_id'; break;
             default: 
                 error_log("Tipo de entidad inválido proporcionado a Documento::getByEntityId: $entityType");
                 $conn->close(); 
                 return $documentos; 
         }
         
         $sql = "SELECT d.*, u.username as uploaded_by_username
                 FROM documentos d 
                 LEFT JOIN usuarios u ON d.id_usuario = u.id_usuario 
                 WHERE d.$columnName = ?";
         if ($onlyMasters && $entityType !== 'servicio') { 
             $sql .= " AND d.servicio_id IS NULL";
         }
         $sql .= " ORDER BY d.tipoDocumento, d.fechaSubida DESC";
         
         $stmt = $conn->prepare($sql);
         if (!$stmt) { error_log("Prepare failed (doc getByEntityId): ".$conn->error); $conn->close(); return $documentos; }
         
         $entityIdValidated = filter_var($entityId, FILTER_VALIDATE_INT);
         if (!$entityIdValidated) { 
             error_log("ID de entidad inválido proporcionado a Documento::getByEntityId para tipo $entityType: $entityId");
             if($stmt) $stmt->close(); 
             $conn->close();
             return $documentos; 
         }
         
         $stmt->bind_param("i", $entityIdValidated);
         if ($stmt->execute()) { 
             $result = $stmt->get_result();
             if ($result) { 
                 while ($row = $result->fetch_assoc()) { 
                     $documentos[] = $row;
                 } 
                 $result->free();
             } 
         } else { 
             error_log("Error execute (doc getByEntityId): " . $stmt->error);
         }
         
         if($stmt) $stmt->close();
         $conn->close();
         return $documentos;
    }

    public static function delete($id) {
        $conn = dbConnect();
        if (!$conn) return false; 
        
        $conn->begin_transaction(); 
        $result = false; 
        
        try { 
            $stmt_get = $conn->prepare("SELECT rutaArchivo FROM documentos WHERE id_documento = ?");
            if (!$stmt_get) throw new Exception("Error al preparar consulta de obtención de ruta: " . $conn->error); 
            $stmt_get->bind_param("i", $id); 
            $stmt_get->execute();
            $result_get = $stmt_get->get_result();
            $documento = ($result_get && $result_get->num_rows === 1) ? $result_get->fetch_assoc() : null; 
            $stmt_get->close();
            if (!$documento) throw new Exception("Documento con ID $id no encontrado para eliminar."); 
            
            $rutaArchivoRelativa = $documento['rutaArchivo'];
            $stmt_del = $conn->prepare("DELETE FROM documentos WHERE id_documento = ?");
            if (!$stmt_del) throw new Exception("Error al preparar consulta de eliminación: " . $conn->error);
            $stmt_del->bind_param("i", $id); 
            
            if (!$stmt_del->execute()) {
                if ($conn->errno == 1451) { 
                    $_SESSION['error_details'] = 'No se puede eliminar el documento, está asociado a otros registros.';
                    throw new Exception("Error FK al eliminar documento ID $id.");
                }
                throw new Exception("Error al ejecutar eliminación en DB: " . $stmt_del->error);
            }
            $stmt_del->close();
            
            $fullPath = realpath(UPLOADS_BASE_DIR . $rutaArchivoRelativa);
            if ($fullPath && strpos($fullPath, realpath(UPLOADS_BASE_DIR)) === 0 && is_file($fullPath)) { 
                if (!unlink($fullPath)) { 
                    error_log("ADVERTENCIA: No se pudo eliminar el archivo físico del documento ID $id: $fullPath");
                }
            } else { 
                error_log("ADVERTENCIA: Archivo físico no encontrado para documento ID $id: $rutaArchivoRelativa");
            } 
            
            $conn->commit();
            $result = true;
        } catch (Exception $e) { 
            $conn->rollback();
            error_log("Error en transacción Documento::delete para ID $id: " . $e->getMessage());
            $_SESSION['error_details'] = $_SESSION['error_details'] ?? ("Error interno al intentar eliminar el documento."); 
            $result = false;
        } finally { 
            if ($conn) $conn->close();
        } 
        return $result;
    }
    
    // --- INICIO DE MODIFICACIÓN: Devolver ID en lugar de true ---
    private static function getDocumentStatus($entityId, $entityColumn, $docTypes) {
        $conn = dbConnect();
        if (!$conn) return array_fill_keys($docTypes, false);

        $status = array_fill_keys($docTypes, false);
        
        // Obtenemos el ID del documento más reciente para cada tipo
        $sql = "SELECT tipoDocumento, MAX(id_documento) as id_documento
                FROM documentos 
                WHERE {$entityColumn} = ? AND tipoDocumento IN ('" . implode("','", $docTypes) . "')
                GROUP BY tipoDocumento";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed (getDocumentStatus for {$entityColumn}): " . $conn->error);
            $conn->close();
            return $status;
        }

        $stmt->bind_param("i", $entityId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                if (in_array($row['tipoDocumento'], $docTypes)) {
                    $status[$row['tipoDocumento']] = $row['id_documento']; // Devolvemos el ID
                }
            }
        } else {
            error_log("Execute failed (getDocumentStatus for {$entityColumn}): " . $stmt->error);
        }

        $stmt->close();
        $conn->close();
        return $status;
    }

    public static function getDocumentStatusForSocio($socioId) {
        $docTypes = ['ID_OFICIAL_TITULAR', 'CONSTANCIA_FISCAL', 'COMPROBANTE_DOM_GANADERIA', 'TITULO_PROPIEDAD_RANCHO'];
        return self::getDocumentStatus($socioId, 'socio_id', $docTypes);
    }

    public static function getDocumentStatusForEjemplar($ejemplarId) {
        $docTypes = ['PASAPORTE_DIE', 'RESULTADO_ADN', 'CERTIFICADO_INSCRIPCION_LG', 'FOTO_IDENTIFICACION'];
        return self::getDocumentStatus($ejemplarId, 'ejemplar_id', $docTypes);
    }

    public static function getDocumentStatusForServicio($servicioId) {
        $docTypes = ['SOLICITUD_SERVICIO', 'COMPROBANTE_PAGO'];
        return self::getDocumentStatus($servicioId, 'servicio_id', $docTypes);
    }
    // --- FIN DE MODIFICACIÓN ---
}