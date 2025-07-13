<?php
// app/controllers/EjemplaresController.php
// Asegúrate que las rutas a los modelos sean correctas
require_once __DIR__ . '/../models/Ejemplar.php';
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Documento.php'; // Incluir modelo Documento

class EjemplaresController {

    // Helper para verificar sesión
    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user'])) { header("Location: index.php?route=login"); exit; }
        // Puedes añadir verificación de rol aquí si es necesario
    }

    // Helper para procesar subida de UN documento para un ejemplar
    private function handleSingleEjemplarDocUpload($fileInputName, $ejemplarId, $tipoDocumento, $userId) {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 10 * 1024 * 1024; // 10 MB

            // Usar subcarpeta 'ejemplares'
            $uploadResult = Documento::handleUpload($fileInputName, $allowedTypes, $maxFileSize, 'ejemplares');

            if ($uploadResult['status'] === 'success') {
                $docData = [
                    'tipoDocumento' => $tipoDocumento,
                    'nombreArchivoOriginal' => $uploadResult['data']['originalName'],
                    'rutaArchivo' => $uploadResult['data']['savedPath'],
                    'mimeType' => $uploadResult['data']['mimeType'],
                    'sizeBytes' => $uploadResult['data']['size'],
                    'socio_id' => null,
                    'ejemplar_id' => $ejemplarId, // Asociar al ejemplar
                    'servicio_id' => null,
                    'id_usuario' => $userId,
                    'comentarios' => 'Documento maestro de ejemplar.'
                ];
                if (!Documento::store($docData)) {
                     error_log("Error BD al guardar doc {$tipoDocumento} para ejemplar {$ejemplarId}.");
                     $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error DB doc: " . htmlspecialchars($uploadResult['data']['originalName']) . ". ";
                }
                 return true; // Indica que se procesó este intento de subida
            } else {
                 // Error devuelto por handleUpload (tamaño, tipo, permisos, etc.)
                 $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error al subir " . htmlspecialchars($_FILES[$fileInputName]['name']) . ": " . $uploadResult['message'] . ". ";
            }
        } elseif (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_NO_FILE && $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
            // Hubo un error de PHP diferente a "No se subió archivo" y diferente a "OK"
             $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error PHP al subir " . $fileInputName . " (Code: " . $_FILES[$fileInputName]['error'] . "). ";
        }
         // Retorna false si no se subió archivo o hubo error en handleUpload
         return false;
    } // Fin handleSingleEjemplarDocUpload


     // Helper para procesar subida de MÚLTIPLES fotos para un ejemplar
     private function handleMultipleEjemplarPhotos($fileInputName, $ejemplarId, $userId) {
         if (isset($_FILES[$fileInputName]) && is_array($_FILES[$fileInputName]['name'])) {
             $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']; $maxFileSize = 5 * 1024 * 1024;
             $fileCount = count($_FILES[$fileInputName]['name']);
             // Subcarpeta específica para fotos de este ejemplar
             $subfolder = 'ejemplares' . DIRECTORY_SEPARATOR . $ejemplarId . DIRECTORY_SEPARATOR . 'fotos';
             error_log("DEBUG: Procesando $fileCount fotos potenciales para ejemplar $ejemplarId.");

             for ($i = 0; $i < $fileCount; $i++) {
                 // Verificar si hay error para ESTE archivo específico del array múltiple
                 if ($_FILES[$fileInputName]['error'][$i] === UPLOAD_ERR_OK) {
                      error_log("DEBUG: Foto #" . ($i+1) . " ('".$_FILES[$fileInputName]['name'][$i]."') sin error PHP inicial.");
                     // Extraer info del archivo actual del array múltiple
                     $currentFile = [
                         'name' => $_FILES[$fileInputName]['name'][$i],
                         'type' => $_FILES[$fileInputName]['type'][$i], // Usar finfo para validar
                         'tmp_name' => $_FILES[$fileInputName]['tmp_name'][$i],
                         'error' => $_FILES[$fileInputName]['error'][$i],
                         'size' => $_FILES[$fileInputName]['size'][$i]
                     ];

                     // 1. Validar tamaño
                     if ($currentFile['size'] == 0) { $_SESSION['warning'] .= " Foto " . htmlspecialchars($currentFile['name']) . " vacía. "; error_log("ERROR: Foto ".$currentFile['name']." vacía."); continue; }
                     if ($currentFile['size'] > $maxFileSize) { $_SESSION['warning'] .= " Foto " . htmlspecialchars($currentFile['name']) . " excede tamaño. "; error_log("ERROR: Foto ".$currentFile['name']." tamaño inválido (".$currentFile['size'].")."); continue; }

                      // 2. Validar tipo con finfo
                      if (!function_exists('finfo_open')) { $_SESSION['warning'] .= 'Error interno (finfo).'; error_log("ERROR: finfo no existe."); continue; }
                      $finfo = finfo_open(FILEINFO_MIME_TYPE); $mime = finfo_file($finfo, $currentFile['tmp_name']); finfo_close($finfo);
                      if (!in_array($mime, $allowedTypes)) { $_SESSION['warning'] .= " Foto " . htmlspecialchars($currentFile['name']) . " tipo inválido ($mime). "; error_log("ERROR: Foto ".$currentFile['name']." tipo inválido ($mime)."); continue; }

                      // 3. Directorio y nombre único
                       $targetDir = UPLOADS_BASE_DIR . $subfolder . DIRECTORY_SEPARATOR;
                       if (!is_dir($targetDir)) { if (!mkdir($targetDir, 0775, true)) { error_log("ERROR: No se pudo crear dir fotos: $targetDir"); $_SESSION['warning'] .= " Err Permisos "; continue;} else { error_log("DEBUG: Dir fotos creado: $targetDir"); } }
                       if (!is_writable($targetDir)){ error_log("ERROR: Dir fotos no escribible: $targetDir"); $_SESSION['warning'] .= " Err Permisos "; continue; }
                       $fileExtension = strtolower(pathinfo($currentFile['name'], PATHINFO_EXTENSION));
                       try { $uniqueName = 'foto_' . bin2hex(random_bytes(8)) . '.' . $fileExtension; } catch (Exception $e) { $uniqueName = 'foto_' . uniqid('', true) . '.' . $fileExtension; }
                       $safeFilename = $uniqueName; $destinationPath = $targetDir . $safeFilename;
                       // Ruta relativa incluye subcarpetas
                       $relativePath = $subfolder . DIRECTORY_SEPARATOR . $safeFilename;


                      // 4. Mover archivo
                      if (move_uploaded_file($currentFile['tmp_name'], $destinationPath)) {
                           error_log("DEBUG: Foto movida a $destinationPath");
                          // 5. Guardar en BD
                          $docData = [ 'tipoDocumento' => 'FOTO_IDENTIFICACION', 'nombreArchivoOriginal' => $currentFile['name'], 'rutaArchivo' => $relativePath, 'mimeType' => $mime, 'sizeBytes' => $currentFile['size'], 'socio_id' => null, 'ejemplar_id' => $ejemplarId, 'servicio_id' => null, 'id_usuario' => $userId, 'comentarios' => 'Foto ID.' ];
                          error_log("DEBUG: Intentando Documento::store() para foto: " . print_r($docData, true));
                          $storeResultPhoto = Documento::store($docData);
                          if (!$storeResultPhoto) { error_log("ERROR: Falla Documento::store() para foto " . $currentFile['name']); $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error DB foto: " . htmlspecialchars($currentFile['name']) . ". "; }
                          else { error_log("DEBUG: Éxito Documento::store() para foto. ID: " . $storeResultPhoto); }
                      } else {
                          error_log("ERROR: Falla move_uploaded_file para foto {$currentFile['name']} a {$destinationPath}");
                           $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error al guardar foto: " . htmlspecialchars($currentFile['name']) . ". ";
                      }

                 } elseif ($_FILES[$fileInputName]['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                      error_log("ERROR: Error PHP al subir foto #" . ($i+1) . " (Code: " . $_FILES[$fileInputName]['error'][$i] . ")");
                      // Punto y coma añadido aquí
                      $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error PHP foto #" . ($i+1);
                 } else {
                      error_log("DEBUG: Foto #" . ($i+1) . " no subida (UPLOAD_ERR_NO_FILE).");
                 }
             } // end for
         } // end if isset
     } // end function


    /**
     * Muestra el formulario para crear un nuevo ejemplar.
     */
    public function create() {
        $this->checkSession();
        $sociosList = Socio::getActiveSociosForSelect();
        if (empty($sociosList)) { $_SESSION['warning'] = "No hay socios activos registrados."; }
        $pageTitle = 'Registrar Nuevo Ejemplar'; $currentRoute = 'ejemplares/create';
        $contentView = __DIR__ . '/../views/ejemplares/create.php';
        // Pasar $sociosList a la vista a través del layout
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Guarda el nuevo ejemplar y sus documentos iniciales.
     */
    public function store() {
        $this->checkSession();
        $userId = $_SESSION['user']['id_usuario'];
        if(isset($_POST)) {
            // Recoger datos
            $nombre = trim($_POST['nombre'] ?? ''); $raza = trim($_POST['raza'] ?? ''); $fechaNacimiento = trim($_POST['fechaNacimiento'] ?? ''); if(empty($fechaNacimiento)) $fechaNacimiento = null; $numeroRegistro = trim($_POST['numeroRegistro'] ?? ''); $socio_id = filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT); $sexo = trim($_POST['sexo'] ?? ''); $codigo_ejemplar = trim($_POST['codigo_ejemplar'] ?? ''); if(empty($codigo_ejemplar)) $codigo_ejemplar = null; $capa = trim($_POST['capa'] ?? ''); if(empty($capa)) $capa = null; $numero_microchip = trim($_POST['numero_microchip'] ?? ''); if(empty($numero_microchip)) $numero_microchip = null; $numero_certificado = trim($_POST['numero_certificado'] ?? ''); if(empty($numero_certificado)) $numero_certificado = null; $estado = trim($_POST['estado'] ?? 'activo');

            // Validaciones
            $errors = []; if (empty($nombre)) $errors[] = "Nombre obligatorio."; if (empty($numeroRegistro)) $errors[] = "Núm Registro obligatorio."; if (empty($socio_id)) $errors[] = "Socio obligatorio."; if (empty($sexo)) $errors[] = "Sexo obligatorio."; if (!empty($fechaNacimiento) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacimiento)){ $errors[]="Fecha inválida."; $fechaNacimiento=null; } if (!in_array($estado, ['activo', 'inactivo'])) $errors[]="Estado inválido.";
            if (!empty($errors)) { $_SESSION['error'] = implode("<br>", $errors); $_SESSION['form_data'] = $_POST; header("Location: index.php?route=ejemplares/create"); exit; }

            // Preparar array $data
            $data = [ 'nombre' => $nombre, 'raza' => $raza ?: null, 'fechaNacimiento' => $fechaNacimiento, 'numeroRegistro' => $numeroRegistro, 'socio_id' => $socio_id, 'sexo' => $sexo, 'codigo_ejemplar' => $codigo_ejemplar, 'capa' => $capa, 'numero_microchip' => $numero_microchip, 'numero_certificado' => $numero_certificado, 'estado' => $estado, 'id_usuario' => $userId ];

            $ejemplarId = Ejemplar::store($data);

            if($ejemplarId !== false) {
                $_SESSION['message'] = "Ejemplar registrado con ID: " . $ejemplarId . "."; unset($_SESSION['form_data']); error_log("INFO: Ejemplar ID $ejemplarId creado. Procesando docs...");
                // Procesar Documentos
                $this->handleSingleEjemplarDocUpload('pasaporte_file', $ejemplarId, 'PASAPORTE_DIE', $userId); $this->handleSingleEjemplarDocUpload('adn_file', $ejemplarId, 'RESULTADO_ADN', $userId); $this->handleSingleEjemplarDocUpload('cert_lg_file', $ejemplarId, 'CERTIFICADO_INSCRIPCION_LG', $userId); $this->handleMultipleEjemplarPhotos('fotos_file', $ejemplarId, $userId);
                header("Location: index.php?route=ejemplares_index"); exit;
            } else { $error_detail = $_SESSION['error_details'] ?? 'Verifique datos.'; unset($_SESSION['error_details']); $_SESSION['error'] = "Error al registrar ejemplar. " . $error_detail; $_SESSION['form_data'] = $_POST; header("Location: index.php?route=ejemplares/create"); exit; }
        } else { $_SESSION['error'] = "No se recibieron datos."; header("Location: index.php?route=ejemplares/create"); exit; }
    }

    /**
     * Muestra la lista de ejemplares.
     */
    public function index() {
         $this->checkSession(); $ejemplares = Ejemplar::getAll(); $pageTitle = 'Listado de Ejemplares'; $currentRoute = 'ejemplares_index'; $contentView = __DIR__ . '/../views/ejemplares/index.php'; require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Muestra el formulario para editar un ejemplar y sus documentos.
     */
    public function edit($id = null) {
        $this->checkSession(); $ejemplarId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($ejemplarId) { $ejemplar = Ejemplar::getById($ejemplarId); if ($ejemplar) { $sociosList = Socio::getActiveSociosForSelect(); $documentosEjemplar = Documento::getByEntityId('ejemplar', $ejemplarId, true); $pageTitle = 'Editar Ejemplar'; $currentRoute = 'ejemplares/edit'; $contentView = __DIR__ . '/../views/ejemplares/edit.php'; require_once __DIR__ . '/../views/layouts/master.php'; return; } else { $_SESSION['error'] = "Ejemplar no encontrado."; } }
        else { $_SESSION['error'] = "ID inválido."; } header("Location: index.php?route=ejemplares_index"); exit;
    }

    /**
     * Actualiza un ejemplar existente y maneja nuevas subidas de documentos.
     */
    public function update() {
        $this->checkSession(); $userId = $_SESSION['user']['id_usuario'];
        if(isset($_POST['id_ejemplar'])) {
            $id = filter_input(INPUT_POST, 'id_ejemplar', FILTER_VALIDATE_INT);
             if (!$id) { $_SESSION['error'] = "ID inválido."; header("Location: index.php?route=ejemplares_index"); exit; }

            // Recoger datos (sin numeroRegistro)
            $nombre = trim($_POST['nombre'] ?? '');
            $raza = trim($_POST['raza'] ?? '');
            $fechaNacimiento = trim($_POST['fechaNacimiento'] ?? ''); if(empty($fechaNacimiento)) $fechaNacimiento = null;
            // $numeroRegistro = trim($_POST['numeroRegistro'] ?? ''); // Ya no se recoge
            $socio_id = filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT);
            $sexo = trim($_POST['sexo'] ?? '');
            $codigo_ejemplar = trim($_POST['codigo_ejemplar'] ?? ''); if(empty($codigo_ejemplar)) $codigo_ejemplar = null;
            $capa = trim($_POST['capa'] ?? ''); if(empty($capa)) $capa = null;
            $numero_microchip = trim($_POST['numero_microchip'] ?? ''); if(empty($numero_microchip)) $numero_microchip = null;
            $numero_certificado = trim($_POST['numero_certificado'] ?? ''); if(empty($numero_certificado)) $numero_certificado = null;
            $estado = trim($_POST['estado'] ?? '');

            // Validaciones (SIN numeroRegistro)
            $errors = [];
            if(empty($nombre)) $errors[]="Nombre";
            // if(empty($numeroRegistro)) $errors[]="Num Reg"; // <<< LÍNEA ELIMINADA >>>
            if(empty($socio_id)) $errors[]="Socio";
            if(empty($sexo)) $errors[]="Sexo";
            if (!empty($fechaNacimiento) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacimiento)){ $errors[]="Fecha Nacimiento Inválida"; $fechaNacimiento=null; }
            if (!in_array($estado, ['activo', 'inactivo'])) $errors[]="Estado Inválido";
            // ... otras validaciones que necesites ...

            if (!empty($errors)) {
                $_SESSION['error'] = "Campos obligatorios/inválidos: ".implode(", ", $errors).".";
                header("Location: index.php?route=ejemplares/edit&id=" . $id); exit;
            }

            // Preparar array data (SIN numeroRegistro)
            $data = [
                'nombre' => $nombre, 'raza' => $raza ?: null, 'fechaNacimiento' => $fechaNacimiento,
                /* 'numeroRegistro' => $numeroRegistro, // Eliminado */ 'socio_id' => $socio_id, 'sexo' => $sexo,
                'codigo_ejemplar' => $codigo_ejemplar, 'capa' => $capa, 'numero_microchip' => $numero_microchip,
                'numero_certificado' => $numero_certificado, 'estado' => $estado, 'id_usuario' => $userId
            ];

            error_log("INFO: Actualizando ejemplar ID $id...");
            if(Ejemplar::update($id, $data)) {
                 $_SESSION['message'] = "Ejemplar actualizado.";
                 // Procesar NUEVOS Documentos Subidos
                 $this->handleSingleEjemplarDocUpload('pasaporte_file', $id, 'PASAPORTE_DIE', $userId);
                 $this->handleSingleEjemplarDocUpload('adn_file', $id, 'RESULTADO_ADN', $userId);
                 $this->handleSingleEjemplarDocUpload('cert_lg_file', $id, 'CERTIFICADO_INSCRIPCION_LG', $userId);
                 $this->handleMultipleEjemplarPhotos('fotos_file', $id, $userId);
            } else {
                 $error_detail = $_SESSION['error_details'] ?? 'Error al guardar.'; unset($_SESSION['error_details']);
                 $_SESSION['error'] = "Error al actualizar. " . $error_detail;
                 header("Location: index.php?route=ejemplares/edit&id=" . $id); exit;
            }
        } else {
            $_SESSION['error'] = "Datos inválidos.";
            header("Location: index.php?route=ejemplares_index"); exit;
        }
        // Redirigir al listado después de actualizar
        header("Location: index.php?route=ejemplares_index");
        exit;
    }
    /**
     * Elimina un ejemplar existente.
     */
    public function delete($id = null) {
         $this->checkSession(); $ejemplarId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
         if ($ejemplarId) { if (Ejemplar::delete($ejemplarId)) { $_SESSION['message'] = "Ejemplar eliminado."; } else { $_SESSION['error'] = "Error al eliminar. " . ($_SESSION['error_details'] ?? ''); unset($_SESSION['error_details']); } }
         else { $_SESSION['error'] = "ID inválido."; } header("Location: index.php?route=ejemplares_index"); exit;
    }

} // Fin clase
?>