<?php
// app/models/Documento.php
require_once __DIR__ . '/../../config/config.php'; // Constante UPLOADS_BASE_DIR definida en config.php

class Documento {

    /**
     * Maneja la subida de un archivo al servidor.
     * @param string $fileInputName El 'name' del input file del formulario.
     * @param array $allowedTypes Array de MIME types permitidos.
     * @param int $maxFileSize Tamaño máximo permitido en bytes.
     * @param string $subfolder Subcarpeta dentro de UPLOADS_BASE_DIR (ej. 'socios', 'ejemplares/ID/fotos').
     * @param array|null $filesArray Opcional: El array de archivos a procesar (por defecto usa $_FILES global).
     * Esto es útil para manejar subidas de arrays múltiples (fotos_file[]).
     * @return array ['status'=>..., 'data' => ...] o ['status'=>'error', 'message'=>...]
     */
    public static function handleUpload($fileInputName, $allowedTypes = [], $maxFileSize = 5000000, $subfolder = '', $filesArray = null) {
        // CORREGIDO: Usar el $filesArray proporcionado, o el $_FILES global si no se proporciona.
        $sourceFiles = ($filesArray !== null) ? $filesArray : $_FILES;

        // Verificar si el archivo existe en el array de fuentes.
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
            // Limpiar la subcarpeta para evitar Path Traversal
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
            $uniqueName = uniqid('file_', true) . '.' . $fileExtension; // Fallback si random_bytes falla
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


    /**
     * Guarda la información de un documento (VALIDADO POR DEFECTO).
     * @param array $data Datos del documento.
     * @return int|false Retorna el ID del documento insertado o false en caso de error.
     */
    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) { return false; }

        // Cambiado FALSE por TRUE para 'validado'
        $sql = "INSERT INTO documentos (
                    tipoDocumento, nombreArchivoOriginal, rutaArchivo, mimeType, sizeBytes,
                    socio_id, ejemplar_id, servicio_id,
                    id_usuario, comentarios, validado, fechaSubida
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE, NOW())"; // validado=TRUE por defecto

        $stmt = $conn->prepare($sql);
        if (!$stmt) { error_log("Prepare failed (doc store): " . $conn->error); $_SESSION['error_details'] = 'Error interno al preparar la inserción del documento.'; $conn->close(); return false; }

        $tipoDocumento = $data['tipoDocumento']; 
        $nombreOriginal = $data['nombreArchivoOriginal']; 
        $rutaArchivo = $data['rutaArchivo'];
        $mimeType = $data['mimeType'] ?? null; 
        $sizeBytes = $data['sizeBytes'] ?? null; 
        $socio_id = !empty($data['socio_id']) ? filter_var($data['socio_id'], FILTER_VALIDATE_INT) : null;
        $ejemplar_id = !empty($data['ejemplar_id']) ? filter_var($data['ejemplar_id'], FILTER_VALIDATE_INT) : null; 
        $servicio_id = !empty($data['servicio_id']) ? filter_var($data['servicio_id'], FILTER_VALIDATE_INT) : null; 
        $id_usuario = $data['id_usuario'];
        $comentarios = $data['comentarios'] ?: null;

        // Validación de al menos una asociación
        if ($socio_id === null && $ejemplar_id === null && $servicio_id === null) { 
            error_log("Error: Intento de guardar documento sin asociación a entidad (socio, ejemplar, servicio).");
            $_SESSION['error_details'] = "Error: El documento debe estar asociado a un Socio, Ejemplar o Servicio."; 
            if ($stmt) $stmt->close(); 
            $conn->close(); 
            return false;
        }

        $types = "ssssiiiiis"; // 10 tipos (sin validado ni fecha)
        $stmt->bind_param($types,
            $tipoDocumento, $nombreOriginal, $rutaArchivo, $mimeType, $sizeBytes,
            $socio_id, $ejemplar_id, $servicio_id, $id_usuario, $comentarios
        );
        $result = false; 
        $newId = false;
        try {
            $result = $stmt->execute();
            if ($result) { 
                $newId = $conn->insert_id; 
                if ($newId == 0) { 
                    $result=false; 
                    $_SESSION['error_details'] = 'Error al obtener el ID del documento insertado.';
                } 
            } else { 
                error_log("Execute failed (doc store): " . $stmt->error);
                $_SESSION['error_details'] = "Error de base de datos al guardar el documento: " . $stmt->error; 
                $result=false; 
            }
        } catch (mysqli_sql_exception $e) { 
            error_log("Exception (doc store): " . $e->getMessage());
            // Podrías añadir lógica para errores de duplicados si aplica a tus documentos (ej. un tipo de doc único por entidad)
            $_SESSION['error_details'] = 'Error de base de datos al guardar el documento (' . $e->getCode() . '): ' . $e->getMessage(); 
            $result = false; 
        }

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
        $conn = dbConnect();
        if (!$conn) { return null; }
        // Seleccionamos 'validado' y 'comentarios' aunque no los gestionemos activamente, por si se usan para mostrar info.
        $stmt = $conn->prepare("SELECT d.*, u.username as uploaded_by_username
                                FROM documentos d
                                LEFT JOIN usuarios u ON d.id_usuario = u.id_usuario
                                WHERE d.id_documento = ?");
        if (!$stmt) { error_log("Prepare failed (doc getById): ".$conn->error); $conn->close(); return null; }
        $stmt->bind_param("i", $id);
        $executeResult = $stmt->execute(); 
        $documento = null;
        if($executeResult){ 
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


    /**
     * Obtiene documentos asociados a un ID específico.
     * @param string $entityType 'socio', 'ejemplar', o 'servicio'.
     * @param int $entityId ID de la entidad.
     * @param bool $onlyMasters Si es true, solo busca docs maestros (servicio_id IS NULL).
     * @return array Retorna un array de documentos (puede estar vacío).
     */
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
         
         // Si se piden solo documentos maestros (y no es un servicio, ya que los servicios siempre tienen docs "maestros" asociados a ellos mismos)
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
         $executeResult = $stmt->execute();
         
         if ($executeResult) { 
             $result = $stmt->get_result(); 
             if ($result) { 
                 while ($row = $result->fetch_assoc()) { 
                     $documentos[] = $row; 
                 } 
                 $result->free();
             } else { 
                 error_log("Error get_result (doc getByEntityId): " . $stmt->error);
             } 
         } else { 
             error_log("Error execute (doc getByEntityId): " . $stmt->error);
         }
         
         if($stmt) $stmt->close(); 
         $conn->close();
         return $documentos; // Retorno explícito final array
    }


    /**
     * Elimina un registro de documento de la BD y su archivo físico.
     * @param int $id ID del documento a eliminar.
     * @return bool True si se eliminó correctamente, False si falla.
     */
    public static function delete($id) {
        $conn = dbConnect();
        if (!$conn) return false; 
        
        $conn->begin_transaction(); 
        $result = false; 
        
        try { 
            // Obtener la ruta del archivo físico antes de borrar el registro de la DB
            $stmt_get = $conn->prepare("SELECT rutaArchivo FROM documentos WHERE id_documento = ?");
            if (!$stmt_get) throw new Exception("Error al preparar consulta de obtención de ruta (Documento delete): " . $conn->error); 
            $stmt_get->bind_param("i", $id); 
            $stmt_get->execute(); 
            $result_get = $stmt_get->get_result();
            $documento = ($result_get && $result_get->num_rows === 1) ? $result_get->fetch_assoc() : null; 
            $stmt_get->close();
            
            if (!$documento) throw new Exception("Documento con ID $id no encontrado para eliminar."); 
            $rutaArchivoRelativa = $documento['rutaArchivo'];
            
            // Eliminar el registro de la base de datos
            $stmt_del = $conn->prepare("DELETE FROM documentos WHERE id_documento = ?"); 
            if (!$stmt_del) throw new Exception("Error al preparar consulta de eliminación (Documento delete): " . $conn->error);
            $stmt_del->bind_param("i", $id); 
            $result_db = $stmt_del->execute(); 
            if (!$result_db) {
                // Capturar error de clave foránea si el documento está referenciado en otro lugar (aunque no debería)
                if ($conn->errno == 1451) { 
                    $_SESSION['error_details'] = 'No se puede eliminar el documento, está asociado a otros registros (ej. un servicio).';
                    throw new Exception("Error FK al eliminar documento ID $id.");
                }
                throw new Exception("Error al ejecutar eliminación en DB (Documento delete): " . $stmt_del->error); 
            }
            $stmt_del->close();
            
            // Intentar eliminar el archivo físico
            // Asegurarse de que la ruta completa sea segura y esté dentro del directorio de uploads
            $fullPath = realpath(UPLOADS_BASE_DIR . $rutaArchivoRelativa); 
            if ($fullPath && strpos($fullPath, realpath(UPLOADS_BASE_DIR)) === 0 && is_file($fullPath)) { 
                if (!unlink($fullPath)) { 
                    error_log("ADVERTENCIA: No se pudo eliminar el archivo físico del documento ID $id: $fullPath");
                    // No lanzamos una excepción fatal aquí, ya que el registro de DB ya se borró
                    // Podríamos considerar marcarlo para borrado manual o reintentos
                } else { 
                    error_log("INFO: Archivo físico del documento ID $id eliminado: $fullPath"); 
                } 
            } else { 
                error_log("ADVERTENCIA: Archivo físico no encontrado o ruta inválida para documento ID $id: $rutaArchivoRelativa"); 
                // Esto podría ocurrir si el archivo ya fue borrado o la ruta en la DB es incorrecta
            } 
            
            $conn->commit(); // Confirmar la transacción
            $result = true; 
        } catch (Exception $e) { 
            $conn->rollback(); // Revertir la transacción si algo falla
            error_log("Error en transacción Documento::delete para ID $id: " . $e->getMessage());
            // Si $_SESSION['error_details'] ya fue establecido por una FK, no lo sobrescribimos
            $_SESSION['error_details'] = $_SESSION['error_details'] ?? ("Error interno al intentar eliminar el documento: " . $e->getMessage()); 
            $result = false; 
        } finally { 
            if ($conn) $conn->close(); 
        } 
        return $result;
    }

} // Fin clase Documento