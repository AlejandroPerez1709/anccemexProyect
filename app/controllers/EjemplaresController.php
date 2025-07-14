<?php
// app/controllers/EjemplaresController.php
// Asegúrate que las rutas a los modelos sean correctas
require_once __DIR__ . '/../models/Ejemplar.php';
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Documento.php'; // Incluir modelo Documento
// Se incluye config.php para usar la función global check_permission()
require_once __DIR__ . '/../../config/config.php'; 

class EjemplaresController {

    // Los métodos de verificación de sesión se han consolidado en check_permission()

    // Helper para procesar subida de UN documento para un ejemplar
    private function handleSingleEjemplarDocUpload($fileInputName, $ejemplarId, $tipoDocumento, $userId) {
        // La función Documento::handleUpload() espera que $_FILES ya contenga la estructura del archivo.
        // Aquí no necesitamos pasar un quinto argumento, ya que handleUpload() leerá directamente de $_FILES
        // bajo el nombre $fileInputName.
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 10 * 1024 * 1024; // 10 MB

            // Documento::handleUpload() ya sabe cómo manejar $_FILES globalmente.
            // Le pasamos el nombre del input de archivo.
            $uploadResult = Documento::handleUpload($fileInputName, $allowedTypes, $maxFileSize, 'ejemplares');
            if ($uploadResult['status'] === 'success') {
                $docData = [
                    'tipoDocumento' => $tipoDocumento,
                    'nombreArchivoOriginal' => $uploadResult['data']['originalName'],
                    'rutaArchivo' => $uploadResult['data']['savedPath'], 
                    'mimeType' => $uploadResult['data']['mimeType'],
                    'sizeBytes' => $uploadResult['data']['size'],
                    'socio_id' => null, // Este documento se asocia al ejemplar, no directamente al socio
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
         // Verificamos si el input múltiple existe y si tiene archivos
         if (isset($_FILES[$fileInputName]) && is_array($_FILES[$fileInputName]['name']) && !empty($_FILES[$fileInputName]['name'][0])) {
             $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
             $maxFileSize = 5 * 1024 * 1024; // 5 MB para fotos individuales
             $fileCount = count($_FILES[$fileInputName]['name']);
             
             // Subcarpeta específica para fotos de este ejemplar
             $subfolder = 'ejemplares' . DIRECTORY_SEPARATOR . $ejemplarId . DIRECTORY_SEPARATOR . 'fotos';
             error_log("DEBUG: Procesando $fileCount fotos potenciales para ejemplar $ejemplarId.");

             for ($i = 0; $i < $fileCount; $i++) {
                 // Verificar si este archivo específico del array múltiple fue subido sin errores
                 if ($_FILES[$fileInputName]['error'][$i] === UPLOAD_ERR_OK) {
                      error_log("DEBUG: Foto #" . ($i+1) . " ('".$_FILES[$fileInputName]['name'][$i]."') sin error PHP inicial.");

                     // CORREGIDO: Creamos un array temporal para simular la estructura de $_FILES para un solo archivo
                     // para que Documento::handleUpload() lo procese como si fuera un solo input de archivo.
                     $singleFileArray = [
                         'name' => $_FILES[$fileInputName]['name'][$i],
                         'type' => $_FILES[$fileInputName]['type'][$i],
                         'tmp_name' => $_FILES[$fileInputName]['tmp_name'][$i],
                         'error' => $_FILES[$fileInputName]['error'][$i],
                         'size' => $_FILES[$fileInputName]['size'][$i]
                     ];

                     // CORREGIDO: Modificada la llamada a Documento::handleUpload()
                     // La función handleUpload necesita un array como $_FILES['nombre_input']
                     // Por lo tanto, le pasamos 'dummy_name' como el nombre del input, y en lugar
                     // de usar $_FILES globalmente, le pasamos el array $singleFileArray
                     // como el array completo de $_FILES para que lo procese.
                     $uploadResult = Documento::handleUpload('dummy_name', $allowedTypes, $maxFileSize, $subfolder, ['dummy_name' => $singleFileArray]);

                     if ($uploadResult['status'] === 'success') {
                          // Guardar en BD
                          $docData = [
                              'tipoDocumento' => 'FOTO_IDENTIFICACION',
                              'nombreArchivoOriginal' => $uploadResult['data']['originalName'],
                              'rutaArchivo' => $uploadResult['data']['savedPath'],
                              'mimeType' => $uploadResult['data']['mimeType'],
                              'sizeBytes' => $uploadResult['data']['size'],
                              'socio_id' => null,
                              'ejemplar_id' => $ejemplarId,
                              'servicio_id' => null,
                              'id_usuario' => $userId,
                              'comentarios' => 'Foto ID.'
                          ];
                          error_log("DEBUG: Intentando Documento::store() para foto: " . print_r($docData, true));
                          $storeResultPhoto = Documento::store($docData);
                          if (!$storeResultPhoto) { 
                              error_log("ERROR: Falla Documento::store() para foto " . $singleFileArray['name']); 
                              $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error DB foto: " . htmlspecialchars($singleFileArray['name']) . ". ";
                          } else { 
                              error_log("DEBUG: Éxito Documento::store() para foto. ID: " . $storeResultPhoto);
                          }
                      } else {
                          // Error devuelto por handleUpload (tamaño, tipo, permisos, etc.)
                          $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error al subir " . htmlspecialchars($singleFileArray['name']) . ": " . $uploadResult['message'] . ". ";
                          error_log("ERROR: Falla handleUpload para foto {$singleFileArray['name']}: {$uploadResult['message']}");
                      }

                 } elseif ($_FILES[$fileInputName]['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                      error_log("ERROR: Error PHP al subir foto #" . ($i+1) . " (Code: " . $_FILES[$fileInputName]['error'][$i] . ")");
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
        check_permission();
        // Recuperar datos del formulario si hubo un error de validación previo para repoblar
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']); // Limpiar después de recuperarlos

        $sociosList = Socio::getActiveSociosForSelect();
        if (empty($sociosList)) { $_SESSION['warning'] = "No hay socios activos registrados. Por favor, registre uno primero."; }
        
        $pageTitle = 'Registrar Nuevo Ejemplar'; 
        $currentRoute = 'ejemplares/create';
        $contentView = __DIR__ . '/../views/ejemplares/create.php';
        // Pasar $sociosList y $formData a la vista a través del layout
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Guarda el nuevo ejemplar y sus documentos iniciales.
     */
    public function store() {
        check_permission();
        $userId = $_SESSION['user']['id_usuario'];
        
        // Guardar todos los datos POST en sesión al inicio para repoblar el formulario si hay errores
        $_SESSION['form_data'] = $_POST;

        if(isset($_POST)) {
            // Recoger y limpiar datos
            $nombre = trim($_POST['nombre'] ?? '');
            $raza = trim($_POST['raza'] ?? ''); 
            $fechaNacimiento = trim($_POST['fechaNacimiento'] ?? ''); 
            $socio_id = filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT); 
            $sexo = trim($_POST['sexo'] ?? ''); 
            $codigo_ejemplar = trim($_POST['codigo_ejemplar'] ?? ''); 
            $capa = trim($_POST['capa'] ?? ''); 
            $numero_microchip = trim($_POST['numero_microchip'] ?? ''); 
            $numero_certificado = trim($_POST['numero_certificado'] ?? ''); 
            $estado = trim($_POST['estado'] ?? 'activo');

            // --- Validaciones del Lado del Servidor (MEJORADAS) ---
            $errors = [];

            // 1. Validaciones de campos obligatorios
            if (empty($nombre)) $errors[] = "El nombre del ejemplar es obligatorio.";
            if (empty($socio_id)) $errors[] = "Debe seleccionar un socio propietario.";
            if (empty($sexo)) $errors[] = "El sexo del ejemplar es obligatorio.";
            if (empty($estado)) $errors[] = "El estado del ejemplar es obligatorio."; // Aunque tiene un valor por defecto

            // 2. Validar socio_id: que exista y esté activo
            if ($socio_id && !Socio::getById($socio_id)) {
                $errors[] = "El socio seleccionado no es válido o no existe.";
            }

            // 3. Validación de formato de nombre (letras, espacios, números, guiones, puntos, comas)
            if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.\,\-]+$/u', $nombre)) $errors[] = "El nombre contiene caracteres inválidos.";
            
            // 4. Validación de Raza (letras, espacios, números, guiones, puntos)
            if (!empty($raza) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-\.]+$/u', $raza)) $errors[] = "La raza contiene caracteres inválidos.";

            // 5. Validación de Capa (letras, espacios, números, guiones, puntos)
            if (!empty($capa) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s\-\.]+$/u', $capa)) $errors[] = "La capa contiene caracteres inválidos.";

            // 6. Validación de Código Ejemplar (letras, números, guiones)
            if (!empty($codigo_ejemplar) && !preg_match('/^[A-Za-z0-9\-]+$/', $codigo_ejemplar)) $errors[] = "El código de ejemplar contiene caracteres inválidos.";

            // 7. Validación de Número de Microchip (solo números)
            if (!empty($numero_microchip) && !preg_match('/^[0-9]+$/', $numero_microchip)) $errors[] = "El número de microchip solo debe contener números.";

            // 8. Validación de Número de Certificado (letras, números, guiones)
            if (!empty($numero_certificado) && !preg_match('/^[A-Za-z0-9\-]+$/', $numero_certificado)) $errors[] = "El número de certificado contiene caracteres inválidos.";

            // 9. Validación de Sexo (solo valores permitidos)
            $sexos_permitidos = ['Macho', 'Hembra'];
            if (!empty($sexo) && !in_array($sexo, $sexos_permitidos)) {
                $errors[] = "El sexo seleccionado no es válido.";
            }

            // 10. Validación de Fecha de Nacimiento (formato, no futura)
            if (!empty($fechaNacimiento)) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacimiento)) {
                    $errors[] = "El formato de la Fecha de Nacimiento es inválido (debe ser AAAA-MM-DD).";
                } else {
                    list($y, $m, $d) = explode('-', $fechaNacimiento);
                    if (!checkdate($m, $d, $y)) {
                        $errors[] = "La Fecha de Nacimiento no es una fecha válida.";
                    } else {
                        $hoy = date('Y-m-d');
                        if ($fechaNacimiento > $hoy) {
                            $errors[] = "La Fecha de Nacimiento no puede ser futura.";
                        }
                    }
                }
            } else {
                $fechaNacimiento = null; // Si no es obligatorio y está vacío
            }

            // 11. Validación de Estado (solo valores permitidos)
            $estados_permitidos = ['activo', 'inactivo'];
            if (!in_array($estado, $estados_permitidos)) {
                $errors[] = "El estado seleccionado no es válido.";
            }

            // Si hay errores, guardarlos en sesión y redirigir
            if (!empty($errors)) {
                $_SESSION['error'] = "Se encontraron los siguientes problemas:<br>" . implode("<br>", $errors);
                header("Location: index.php?route=ejemplares/create");
                exit;
            }

            // Preparar array $data
            $data = [ 
                'nombre' => $nombre, 
                'raza' => $raza ?: null, 
                'fechaNacimiento' => $fechaNacimiento, 
                'socio_id' => $socio_id, 
                'sexo' => $sexo, 
                'codigo_ejemplar' => $codigo_ejemplar ?: null, 
                'capa' => $capa ?: null, 
                'numero_microchip' => $numero_microchip ?: null, 
                'numero_certificado' => $numero_certificado ?: null, 
                'estado' => $estado, 
                'id_usuario' => $userId 
            ];
            
            // Intentar guardar el ejemplar
            $ejemplarId = Ejemplar::store($data);

            if($ejemplarId !== false) {
                $_SESSION['message'] = "Ejemplar registrado con ID: " . $ejemplarId . "."; 
                unset($_SESSION['form_data']); // Limpiar datos del formulario en sesión

                error_log("INFO: Ejemplar ID $ejemplarId creado. Procesando docs...");
                // Procesar Documentos (Si hay warnings en la subida, se añadirán a la sesión)
                // Aquí, Documento::handleUpload() usa el $_FILES globalmente, no necesita el quinto parámetro.
                $this->handleSingleEjemplarDocUpload('pasaporte_file', $ejemplarId, 'PASAPORTE_DIE', $userId);
                $this->handleSingleEjemplarDocUpload('adn_file', $ejemplarId, 'RESULTADO_ADN', $userId);
                $this->handleSingleEjemplarDocUpload('cert_lg_file', $ejemplarId, 'CERTIFICADO_INSCRIPCION_LG', $userId);
                // CORREGIDO: La llamada a handleMultipleEjemplarPhotos() ya no necesita el quinto parámetro,
                // ya que la adaptación para Documento::handleUpload se hizo dentro de handleMultipleEjemplarPhotos.
                $this->handleMultipleEjemplarPhotos('fotos_file', $ejemplarId, $userId);
                
                header("Location: index.php?route=ejemplares_index"); 
                exit;
            } else { 
                // Si hubo un error en el modelo (ej. DB), mostrar error genérico o específico
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al guardar el ejemplar. Verifique los datos o intente más tarde.'; 
                unset($_SESSION['error_details']); 
                $_SESSION['error'] = "Error al registrar ejemplar: " . $error_detail;
                // Mantener $_SESSION['form_data'] para que el formulario se repueble
                header("Location: index.php?route=ejemplares/create"); 
                exit; 
            }
        } else { 
            $_SESSION['error'] = "No se recibieron datos del formulario.";
            header("Location: index.php?route=ejemplares/create"); 
            exit; 
        }
    }

    /**
     * Muestra la lista de ejemplares.
     */
    public function index() {
         check_permission(); 
         $ejemplares = Ejemplar::getAll();
         $pageTitle = 'Listado de Ejemplares'; 
         $currentRoute = 'ejemplares_index'; 
         $contentView = __DIR__ . '/../views/ejemplares/index.php'; 
         require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Muestra el formulario para editar un ejemplar y sus documentos.
     */
    public function edit($id = null) {
        check_permission();
        
        $ejemplarId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($ejemplarId) { 
            $ejemplar = Ejemplar::getById($ejemplarId); 
            if ($ejemplar) { 
                // Si se cargan datos de un ejemplar, usarlos para repoblar el formulario
                // Si hubo un error previo en update(), $_SESSION['form_data'] tendrá prioridad
                $formData = $_SESSION['form_data'] ?? $ejemplar; // Repoblar con datos del ejemplar o de la sesión si hubo error
                unset($_SESSION['form_data']); // Limpiar después de usarlos

                $sociosList = Socio::getActiveSociosForSelect();
                $documentosEjemplar = Documento::getByEntityId('ejemplar', $ejemplarId, true); 
                $pageTitle = 'Editar Ejemplar'; 
                $currentRoute = 'ejemplares/edit'; 
                $contentView = __DIR__ . '/../views/ejemplares/edit.php'; 
                // Pasar $formData, $sociosList y $documentosEjemplar a la vista
                require_once __DIR__ . '/../views/layouts/master.php'; 
                return; 
            } else { 
                $_SESSION['error'] = "Ejemplar no encontrado."; 
            } 
        } else { 
            $_SESSION['error'] = "ID inválido."; 
        } 
        header("Location: index.php?route=ejemplares_index"); 
        exit;
    }

    /**
     * Actualiza un ejemplar existente y maneja nuevas subidas de documentos.
     */
    public function update() {
        check_permission(); 
        $userId = $_SESSION['user']['id_usuario'];
        
        // Recuperar el ID al inicio para poder redirigir a la misma página de edición en caso de error
        $id = filter_input(INPUT_POST, 'id_ejemplar', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de ejemplar inválido."; 
            header("Location: index.php?route=ejemplares_index"); 
            exit;
        }
        // Guardar todos los datos POST en sesión para repoblar el formulario si hay errores
        $_SESSION['form_data'] = $_POST; 

        if(isset($_POST)) {
            // Recoger y limpiar datos
            $nombre = trim($_POST['nombre'] ?? '');
            $raza = trim($_POST['raza'] ?? '');
            $fechaNacimiento = trim($_POST['fechaNacimiento'] ?? ''); 
            $socio_id = filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT); 
            $sexo = trim($_POST['sexo'] ?? ''); 
            $codigo_ejemplar = trim($_POST['codigo_ejemplar'] ?? ''); 
            $capa = trim($_POST['capa'] ?? ''); 
            $numero_microchip = trim($_POST['numero_microchip'] ?? ''); 
            $numero_certificado = trim($_POST['numero_certificado'] ?? ''); 
            $estado = trim($_POST['estado'] ?? '');

            // --- Validaciones del Lado del Servidor (similar a store) ---
            $errors = [];

            // 1. Validaciones de campos obligatorios
            if (empty($nombre)) $errors[] = "El nombre del ejemplar es obligatorio.";
            if (empty($socio_id)) $errors[] = "Debe seleccionar un socio propietario.";
            if (empty($sexo)) $errors[] = "El sexo del ejemplar es obligatorio.";
            if (empty($estado)) $errors[] = "El estado del ejemplar es obligatorio.";

            // 2. Validar socio_id: que exista y esté activo
            if ($socio_id && !Socio::getById($socio_id)) {
                $errors[] = "El socio seleccionado no es válido o no existe.";
            }

            // 3. Validación de formato de nombre
            if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.\,\-]+$/u', $nombre)) $errors[] = "El nombre contiene caracteres inválidos.";
            
            // 4. Validación de Raza
            if (!empty($raza) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-\.]+$/u', $raza)) $errors[] = "La raza contiene caracteres inválidos.";

            // 5. Validación de Capa
            if (!empty($capa) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s\-\.]+$/u', $capa)) $errors[] = "La capa contiene caracteres inválidos.";

            // 6. Validación de Código Ejemplar
            if (!empty($codigo_ejemplar) && !preg_match('/^[A-Za-z0-9\-]+$/', $codigo_ejemplar)) $errors[] = "El código de ejemplar contiene caracteres inválidos.";

            // 7. Validación de Número de Microchip
            if (!empty($numero_microchip) && !preg_match('/^[0-9]+$/', $numero_microchip)) $errors[] = "El número de microchip solo debe contener números.";

            // 8. Validación de Número de Certificado
            if (!empty($numero_certificado) && !preg_match('/^[A-Za-z0-9\-]+$/', $numero_certificado)) $errors[] = "El número de certificado contiene caracteres inválidos.";

            // 9. Validación de Sexo
            $sexos_permitidos = ['Macho', 'Hembra'];
            if (!empty($sexo) && !in_array($sexo, $sexos_permitidos)) {
                $errors[] = "El sexo seleccionado no es válido.";
            }

            // 10. Validación de Fecha de Nacimiento
            if (!empty($fechaNacimiento)) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacimiento)) {
                    $errors[] = "El formato de la Fecha de Nacimiento es inválido (debe ser AAAA-MM-DD).";
                } else {
                    list($y, $m, $d) = explode('-', $fechaNacimiento);
                    if (!checkdate($m, $d, $y)) {
                        $errors[] = "La Fecha de Nacimiento no es una fecha válida.";
                    } else {
                        $hoy = date('Y-m-d');
                        if ($fechaNacimiento > $hoy) {
                            $errors[] = "La Fecha de Nacimiento no puede ser futura.";
                        }
                    }
                }
            } else {
                $fechaNacimiento = null;
            }

            // 11. Validación de Estado
            $estados_permitidos = ['activo', 'inactivo'];
            if (!in_array($estado, $estados_permitidos)) {
                $errors[] = "El estado seleccionado no es válido.";
            }

            // Si hay errores, guardarlos en sesión y redirigir
            if (!empty($errors)) {
                $_SESSION['error'] = "Se encontraron los siguientes problemas:<br>" . implode("<br>", $errors);
                header("Location: index.php?route=ejemplares/edit&id=" . $id); 
                exit;
            }

            // Preparar datos para actualizar
            $data = [
                'nombre' => $nombre, 
                'raza' => $raza ?: null, 
                'fechaNacimiento' => $fechaNacimiento, 
                'socio_id' => $socio_id, 
                'sexo' => $sexo, 
                'codigo_ejemplar' => $codigo_ejemplar ?: null, 
                'capa' => $capa ?: null, 
                'numero_microchip' => $numero_microchip ?: null, 
                'numero_certificado' => $numero_certificado ?: null, 
                'estado' => $estado, 
                'id_usuario' => $userId 
            ];
            
            // Intentar actualizar el ejemplar
            if(Ejemplar::update($id, $data)) {
                $_SESSION['message'] = "Ejemplar actualizado.";
                unset($_SESSION['form_data']); // Limpiar datos del formulario en sesión

                // Procesar NUEVOS Documentos Subidos (Si hay warnings, se añadirán a la sesión)
                // No necesita el quinto parámetro
                $this->handleSingleEjemplarDocUpload('pasaporte_file', $id, 'PASAPORTE_DIE', $userId);
                $this->handleSingleEjemplarDocUpload('adn_file', $id, 'RESULTADO_ADN', $userId);
                $this->handleSingleEjemplarDocUpload('cert_lg_file', $id, 'CERTIFICADO_INSCRIPCION_LG', $userId);
                // Corregido: La llamada a handleMultipleEjemplarPhotos() ya no necesita el quinto parámetro.
                $this->handleMultipleEjemplarPhotos('fotos_file', $id, $userId);
            } else { 
                // Si hubo un error en el modelo, mostrar error genérico o específico
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al actualizar el ejemplar. Verifique los datos o intente más tarde.'; 
                unset($_SESSION['error_details']); 
                $_SESSION['error'] = "Error al actualizar. " . $error_detail;
                // Mantener $_SESSION['form_data'] para que el formulario se repueble
                header("Location: index.php?route=ejemplares/edit&id=" . $id); 
                exit; 
            }
        } else { 
            $_SESSION['error'] = "No se recibieron datos del formulario.";
            header("Location: index.php?route=ejemplares/edit&id=" . $id); 
            exit; 
        }

        // Redirigir al listado tras éxito
        header("Location: index.php?route=ejemplares_index"); 
        exit;
    }
    /**
     * Elimina un ejemplar existente.
     */
    public function delete($id = null) {
         check_permission();
         $ejemplarId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
         if ($ejemplarId) { 
             if (Ejemplar::delete($ejemplarId)) { 
                 $_SESSION['message'] = "Ejemplar eliminado.";
             } else { 
                 $_SESSION['error'] = "Error al eliminar. " . ($_SESSION['error_details'] ?? 'Puede que el ejemplar tenga registros asociados o no exista.'); 
                 unset($_SESSION['error_details']); 
             } 
         } else { 
             $_SESSION['error'] = "ID inválido."; 
         } 
         header("Location: index.php?route=ejemplares_index"); 
         exit;
    }

}