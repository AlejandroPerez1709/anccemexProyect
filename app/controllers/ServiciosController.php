<?php
// app/controllers/ServiciosController.php
require_once __DIR__ . '/../models/Servicio.php';
require_once __DIR__ . '/../models/TipoServicio.php';
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Ejemplar.php';
require_once __DIR__ . '/../models/Medico.php';
require_once __DIR__ . '/../models/Documento.php'; // Incluir modelo Documento
// Se incluye config.php para usar la función global check_permission()
require_once __DIR__ . '/../../config/config.php';

class ServiciosController {

    // Los métodos de verificación de sesión se han consolidado en check_permission()

    // Helper para procesar subida de un documento específico para un servicio
    private function handleServicioDocumentUpload($fileInputName, $servicioId, $tipoDocumento, $userId) {
        // Permitir tipos comunes de documentos e imágenes
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxFileSize = 10 * 1024 * 1024; // 10 MB (ajustar si es necesario)

        // Verificar si el archivo fue subido y es válido
        // Documento::handleUpload ahora espera el array $_FILES completo como quinto argumento para inputs de arrays.
        // Pero para inputs individuales, se sigue leyendo de $_FILES globalmente.
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            // Usar subcarpeta 'servicios' y organizar por año/mes
            $subfolder = 'servicios' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m');
            // Llamada a handleUpload sin el quinto parámetro si es un input simple (no array)
            $uploadResult = Documento::handleUpload($fileInputName, $allowedTypes, $maxFileSize, $subfolder);
            if ($uploadResult['status'] === 'success') {
                $docData = [
                    'tipoDocumento' => $tipoDocumento,
                    'nombreArchivoOriginal' => $uploadResult['data']['originalName'],
                    'rutaArchivo' => $uploadResult['data']['savedPath'], // Ruta relativa guardada por handleUpload
                    'mimeType' => $uploadResult['data']['mimeType'],
                    'sizeBytes' => $uploadResult['data']['size'],
                    'socio_id' => null, // Este documento se asocia al servicio, no directamente al socio o ejemplar
                    'ejemplar_id' => null,
                    'servicio_id' => $servicioId, // Asociar al servicio
                    'id_usuario' => $userId,
                    'comentarios' => 'Documento de servicio.'
                ];
                if (!Documento::store($docData)) {
                     error_log("Error BD al guardar doc {$tipoDocumento} para servicio {$servicioId}.");
                     $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error DB doc: " . htmlspecialchars($uploadResult['data']['originalName']) . ". ";
                }
                 return true; // Se procesó el intento de subida
            } else {
                 // Error en la subida física (tamaño, tipo, permisos, etc.)
                 $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error al subir " . htmlspecialchars($_FILES[$fileInputName]['name']) . ": " . $uploadResult['message'] . ". ";
            }
        } elseif (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_NO_FILE) {
            // Hubo un error diferente a "No se subió archivo"
             $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error PHP al subir " . $fileInputName . " (Code: " . $_FILES[$fileInputName]['error'] . "). ";
        }
         // Si no se subió archivo (UPLOAD_ERR_NO_FILE), simplemente no hacemos nada y retornamos false
         return false;
    }


    /**
     * Muestra la lista de servicios
     */
    public function index() {
        check_permission();
        $filters = [];
        // Filtros: asegurar que se validan como enteros si son IDs
        if (!empty($_GET['filtro_estado'])) $filters['estado'] = $_GET['filtro_estado'];
        if (!empty($_GET['filtro_socio_id'])) $filters['socio_id'] = filter_input(INPUT_GET, 'filtro_socio_id', FILTER_VALIDATE_INT);
        if (!empty($_GET['filtro_tipo_id'])) $filters['tipo_servicio_id'] = filter_input(INPUT_GET, 'filtro_tipo_id', FILTER_VALIDATE_INT);

        $servicios = Servicio::getAll($filters); // Obtiene los servicios del modelo

        // Listas para los filtros en la vista
        $sociosList = Socio::getActiveSociosForSelect();
        $tiposServicioList = TipoServicio::getActiveForSelect();

        $pageTitle = 'Listado de Servicios';
        $currentRoute = 'servicios_index';
        $contentView = __DIR__ . '/../views/servicios/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Muestra el formulario para registrar un nuevo servicio
     */
    public function create() {
        check_permission();
        // Recuperar datos del formulario si hubo un error de validación previo para repoblar
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']); // Limpiar después de recuperarlos

        $tiposServicioList = TipoServicio::getActiveForSelect();
        $sociosList = Socio::getActiveSociosForSelect();
        $ejemplares = Ejemplar::getAll(); // Cargar todos para filtrar con JS
        $medicosList = Medico::getActiveMedicosForSelect();
        
        // Preparar datos de tipos de servicio para JS (requiere médico)
        $tiposServicioDataJS = [];
        if (!empty($tiposServicioList)) {
            $allTiposData = TipoServicio::getAll(); // Necesitamos info completa de todos los tipos para el JS
            foreach($allTiposData as $tipo) {
                if (isset($tiposServicioList[$tipo['id_tipo_servicio']])) { // Solo incluir activos
                    $tiposServicioDataJS[$tipo['id_tipo_servicio']] = [
                        'req_medico' => !empty($tipo['requiere_medico'])
                    ];
                }
            }
        }

        if (empty($sociosList)) $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . "No hay socios activos registrados. Por favor, registre uno primero.";
        if (empty($tiposServicioList)) $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . "No hay tipos de servicio activos definidos. Por favor, registre uno primero.";

        $pageTitle = 'Registrar Nuevo Servicio';
        $currentRoute = 'servicios/create';
        $contentView = __DIR__ . '/../views/servicios/create.php';
        // Pasar $formData, $tiposServicioList, $sociosList, $ejemplares, $medicosList, $tiposServicioDataJS a la vista
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Guarda la nueva solicitud de servicio Y los documentos iniciales.
     */
    public function store() {
        check_permission();
        $userId = $_SESSION['user']['id_usuario'];
        
        // Guardar todos los datos POST en sesión al inicio para repoblar el formulario si hay errores
        $_SESSION['form_data'] = $_POST;

        if (isset($_POST)) {
            // Recoger datos del servicio
            $socio_id = filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT);
            $tipo_servicio_id = filter_input(INPUT_POST, 'tipo_servicio_id', FILTER_VALIDATE_INT);
            $ejemplar_id = filter_input(INPUT_POST, 'ejemplar_id', FILTER_VALIDATE_INT);
            $medico_id = filter_input(INPUT_POST, 'medico_id', FILTER_VALIDATE_INT);
            $fechaSolicitud = trim($_POST['fechaSolicitud'] ?? date('Y-m-d'));
            $descripcion = trim($_POST['descripcion'] ?? '');
            $referencia_pago = trim($_POST['referencia_pago'] ?? '');
            
            // --- Validaciones (MEJORADAS) ---
            $errors = [];

            // 1. Validaciones de campos obligatorios
            if (empty($socio_id)) $errors[] = "Debe seleccionar un socio.";
            if (empty($tipo_servicio_id)) $errors[] = "Debe seleccionar un tipo de servicio.";
            if (empty($ejemplar_id)) $errors[] = "Debe seleccionar un ejemplar.";
            if (empty($fechaSolicitud)) $errors[] = "La fecha de solicitud es obligatoria.";

            // 2. Validar socio_id: que exista
            $socio = Socio::getById($socio_id);
            if (!$socio) {
                $errors[] = "El socio seleccionado no es válido o no existe.";
                $socio_id = null; // Invalidar ID si no es válido
            }

            // 3. Validar tipo_servicio_id: que exista y obtener si requiere médico
            $tipoServicio = TipoServicio::getById($tipo_servicio_id);
            if (!$tipoServicio) {
                $errors[] = "El tipo de servicio seleccionado no es válido o no existe.";
                $tipo_servicio_id = null; // Invalidar ID
            } else {
                // Si el tipo de servicio requiere médico y no se seleccionó uno
                if (!empty($tipoServicio['requiere_medico']) && empty($medico_id)) {
                    $errors[] = "Este tipo de servicio requiere que se asigne un médico.";
                }
            }

            // 4. Validar ejemplar_id: que exista y pertenezca al socio
            $ejemplar = Ejemplar::getById($ejemplar_id);
            if (!$ejemplar) {
                $errors[] = "El ejemplar seleccionado no es válido o no existe.";
                $ejemplar_id = null;
            } elseif ($ejemplar['socio_id'] != $socio_id) { // Solo si socio_id es válido
                $errors[] = "El ejemplar seleccionado no pertenece al socio elegido.";
            }

            // 5. Validar medico_id: que exista si fue seleccionado
            if (!empty($medico_id) && !Medico::getById($medico_id)) {
                $errors[] = "El médico seleccionado no es válido o no existe.";
                $medico_id = null;
            }

            // 6. Validación de Fecha de Solicitud (formato, no futura)
            if (!empty($fechaSolicitud)) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaSolicitud)) {
                    $errors[] = "El formato de la Fecha de Solicitud es inválido (debe ser AAAA-MM-DD).";
                } else {
                    list($y, $m, $d) = explode('-', $fechaSolicitud);
                    if (!checkdate($m, $d, $y)) {
                        $errors[] = "La Fecha de Solicitud no es una fecha válida.";
                    } else {
                        $hoy = date('Y-m-d');
                        if ($fechaSolicitud > $hoy) {
                            $errors[] = "La Fecha de Solicitud no puede ser futura.";
                        }
                    }
                }
            }

            // 7. Validación de Referencia de Pago (caracteres alfanuméricos y guiones)
            if (!empty($referencia_pago) && !preg_match('/^[A-Za-z0-9\-]+$/', $referencia_pago)) {
                $errors[] = "La referencia de pago contiene caracteres inválidos.";
            }
            
            // 8. Validación de subida de archivos OBLIGATORIOS
            // Se asume que estos inputs siempre existen en $_FILES (incluso si no se subió archivo)
            if (!isset($_FILES['solicitud_file']) || $_FILES['solicitud_file']['error'] === UPLOAD_ERR_NO_FILE) {
                 $errors[] = "Debe adjuntar la Solicitud de Servicio.";
            }
            if (!isset($_FILES['pago_file']) || $_FILES['pago_file']['error'] === UPLOAD_ERR_NO_FILE) {
                 $errors[] = "Debe adjuntar el Comprobante de Pago.";
            }

            // Si hay errores de validación, redirigir
            if (!empty($errors)) {
                $_SESSION['error'] = "Se encontraron los siguientes problemas:<br>" . implode("<br>", $errors);
                // La redirección volverá al create con formData, para que el usuario no pierda lo ingresado.
                header("Location: index.php?route=servicios/create"); 
                exit;
            }

            // Si llegamos aquí, las validaciones de campos y existencia de archivos básicos pasaron.
            // Ahora intentamos procesar las subidas de archivos.
            // Es importante hacerlo antes de guardar el servicio en la DB para no tener registros huérfanos.
            // Documento::handleUpload devolverá status 'error' si el archivo no es válido (tipo/tamaño)
            $uploadResultSolicitud = Documento::handleUpload('solicitud_file', ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'], 10 * 1024 * 1024, 'servicios' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m'));
            $uploadResultPago = Documento::handleUpload('pago_file', ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'], 10 * 1024 * 1024, 'servicios' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m'));

            $fileErrors = [];
            $solicitudFilePath = null;
            $pagoFilePath = null;

            if ($uploadResultSolicitud['status'] !== 'success') {
                $fileErrors[] = "Error con la Solicitud de Servicio: " . ($uploadResultSolicitud['message'] ?? 'Desconocido');
            } else {
                $solicitudFilePath = $uploadResultSolicitud['data'];
            }

            if ($uploadResultPago['status'] !== 'success') {
                $fileErrors[] = "Error con el Comprobante de Pago: " . ($uploadResultPago['message'] ?? 'Desconocido');
            } else {
                $pagoFilePath = $uploadResultPago['data'];
            }

            if (!empty($fileErrors)) {
                // Si hay errores de subida de archivo, mostramos y volvemos al formulario.
                // Limpiamos los archivos subidos exitosamente para evitar huérfanos.
                if ($uploadResultSolicitud['status'] === 'success' && file_exists(UPLOADS_BASE_DIR . $solicitudFilePath['savedPath'])) {
                    unlink(UPLOADS_BASE_DIR . $solicitudFilePath['savedPath']);
                    error_log("INFO: Archivo de solicitud limpiado debido a error en otro archivo o DB.");
                }
                if ($uploadResultPago['status'] === 'success' && file_exists(UPLOADS_BASE_DIR . $pagoFilePath['savedPath'])) {
                    unlink(UPLOADS_BASE_DIR . $pagoFilePath['savedPath']);
                    error_log("INFO: Archivo de pago limpiado debido a error en otro archivo o DB.");
                }

                $_SESSION['error'] = "Se encontraron problemas al subir los documentos:<br>" . implode("<br>", $fileErrors);
                header("Location: index.php?route=servicios/create");
                exit;
            }

            // Preparar datos para el modelo Servicio
            $data = [ 
                'socio_id' => $socio_id, 
                'ejemplar_id' => $ejemplar_id,
                'tipo_servicio_id' => $tipo_servicio_id, 
                'medico_id' => $medico_id ?: null,
                'estado' => 'Recibido Completo', // Estado inicial ahora que pedimos docs
                'fechaSolicitud' => $fechaSolicitud, 
                'descripcion' => $descripcion ?: null,
                'referencia_pago' => $referencia_pago ?: null,
                'id_usuario_registro' => $userId, 
                'id_usuario_ultima_mod' => $userId,
                'fechaRecepcionDocs' => date('Y-m-d'), // Marcar fecha recepción docs hoy
                'fechaPago' => date('Y-m-d'), // Marcar fecha pago hoy (o tomar de form si se añade)
                'fechaAsignacionMedico' => ($medico_id ? date('Y-m-d') : null),
                'fechaVisitaMedico' => null, 'fechaEnvioLG' => null, 'fechaRecepcionLG' => null, 'fechaFinalizacion' => null, // Otros campos vacíos al crear
                'motivo_rechazo' => null,
            ];
            
            // Intentar guardar datos del servicio en la DB
            $servicioId = Servicio::store($data);

            if ($servicioId !== false) {
                $_SESSION['message'] = "Servicio registrado con ID: " . $servicioId . ".";
                unset($_SESSION['form_data']); // Limpiar datos del formulario en sesión

                // Guardar la información de los documentos en la base de datos (ya subidos físicamente)
                Documento::store([
                    'tipoDocumento' => 'SOLICITUD_SERVICIO',
                    'nombreArchivoOriginal' => $solicitudFilePath['originalName'],
                    'rutaArchivo' => $solicitudFilePath['savedPath'],
                    'mimeType' => $solicitudFilePath['mimeType'],
                    'sizeBytes' => $solicitudFilePath['size'],
                    'socio_id' => null, 'ejemplar_id' => null,
                    'servicio_id' => $servicioId,
                    'id_usuario' => $userId,
                    'comentarios' => 'Solicitud de servicio.'
                ]);
                Documento::store([
                    'tipoDocumento' => 'COMPROBANTE_PAGO',
                    'nombreArchivoOriginal' => $pagoFilePath['originalName'],
                    'rutaArchivo' => $pagoFilePath['savedPath'],
                    'mimeType' => $pagoFilePath['mimeType'],
                    'sizeBytes' => $pagoFilePath['size'],
                    'socio_id' => null, 'ejemplar_id' => null,
                    'servicio_id' => $servicioId,
                    'id_usuario' => $userId,
                    'comentarios' => 'Comprobante de pago.'
                ]);

            } else {
                // Si falla el guardado en DB, limpiamos los archivos que ya se subieron
                if ($uploadResultSolicitud['status'] === 'success' && file_exists(UPLOADS_BASE_DIR . $solicitudFilePath['savedPath'])) {
                    unlink(UPLOADS_BASE_DIR . $solicitudFilePath['savedPath']);
                    error_log("ERROR: Archivo de solicitud limpiado tras fallo en DB: " . $solicitudFilePath['savedPath']);
                }
                if ($uploadResultPago['status'] === 'success' && file_exists(UPLOADS_BASE_DIR . $pagoFilePath['savedPath'])) {
                    unlink(UPLOADS_BASE_DIR . $pagoFilePath['savedPath']);
                    error_log("ERROR: Archivo de pago limpiado tras fallo en DB: " . $pagoFilePath['savedPath']);
                }

                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al guardar el servicio. Verifique los datos o intente más tarde.';
                unset($_SESSION['error_details']);
                $_SESSION['error'] = "Error al registrar el servicio: " . $error_detail;
                // Mantener $_SESSION['form_data'] para que el formulario se repueble
                // Redirigir al formulario de creación (el JavaScript en la vista se encargará de repoblar)
                header("Location: index.php?route=servicios/create"); 
                exit; // Detener aquí
            }
        } else { 
            $_SESSION['error'] = "No se recibieron datos del formulario.";
            header("Location: index.php?route=servicios/create"); 
            exit; 
        }
        
        // Redirigir solo si todo fue exitoso (incluyendo guardado de servicio y documentos)
        header("Location: index.php?route=servicios_index");
        exit;
    }

    /**
     * Muestra el formulario para editar/actualizar un servicio y sus documentos.
     */
    public function edit($id = null) {
        check_permission();
        $servicioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$servicioId) { 
            $_SESSION['error'] = "ID inválido."; 
            header("Location: index.php?route=servicios_index"); 
            exit;
        }

        $servicio = Servicio::getById($servicioId);
        if (!$servicio) { 
            $_SESSION['error'] = "Servicio no encontrado.";
            header("Location: index.php?route=servicios_index"); 
            exit; 
        }

        // Si se cargan datos de un servicio, usarlos para repoblar el formulario
        // Si hubo un error previo en update(), $_SESSION['form_data'] tendrá prioridad
        $formData = $_SESSION['form_data'] ?? $servicio; // Repoblar con datos del servicio o de la sesión si hubo error
        unset($_SESSION['form_data']); // Limpiar después de usarlos

        // Cargar listas y datos necesarios para la vista
        $tiposServicioList = TipoServicio::getActiveForSelect();
        $sociosList = Socio::getActiveSociosForSelect();
        $ejemplares = Ejemplar::getAll(); // Cargar todos para filtrar con JS
        $medicosList = Medico::getActiveMedicosForSelect();
        // Posibles estados para el select de estado (se usa en la vista)
        $posiblesEstados = ['Pendiente Docs/Pago', 'Recibido Completo', 'Pendiente Visita Medico', 'Pendiente Resultado Lab', 'Enviado a LG', 'Pendiente Respuesta LG', 'Completado', 'Rechazado', 'Cancelado'];
        // Obtener Documentos asociados a ESTE servicio
        $documentosServicio = Documento::getByEntityId('servicio', $servicioId);

        // Preparar datos de tipos de servicio para JS (requiere médico) - duplicado de create(), se podría refactorizar
        $tiposServicioDataJS = [];
        if (!empty($tiposServicioList)) {
            $allTiposData = TipoServicio::getAll(); // Necesitamos info completa de todos los tipos para el JS
            foreach($allTiposData as $tipo) {
                if (isset($tiposServicioList[$tipo['id_tipo_servicio']])) { // Solo incluir activos
                    $tiposServicioDataJS[$tipo['id_tipo_servicio']] = [
                        'req_medico' => !empty($tipo['requiere_medico'])
                    ];
                }
            }
        }
        
        $pageTitle = 'Editar/Ver Servicio #' . $servicio['id_servicio'];
        $currentRoute = 'servicios/edit';
        $contentView = __DIR__ . '/../views/servicios/edit.php';
        // Pasar todas las variables necesarias a la vista
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Actualiza un servicio existente y maneja subida de nuevos documentos.
     */
    public function update() {
        check_permission();
        $userId = $_SESSION['user']['id_usuario'];
        
        // Recuperar el ID al inicio para poder redirigir a la misma página de edición en caso de error
        $id = filter_input(INPUT_POST, 'id_servicio', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de servicio inválido."; 
            header("Location: index.php?route=servicios_index"); 
            exit;
        }
        // Guardar todos los datos POST en sesión para repoblar el formulario si hay errores
        $_SESSION['form_data'] = $_POST; 

        if (isset($_POST)) {
            // Recoger TODOS los campos del formulario de edición
            $socio_id = filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT); // Viene de hidden
            $tipo_servicio_id = filter_input(INPUT_POST, 'tipo_servicio_id', FILTER_VALIDATE_INT); // Viene de hidden
            $ejemplar_id = filter_input(INPUT_POST, 'ejemplar_id', FILTER_VALIDATE_INT); // Viene de hidden
            $medico_id = filter_input(INPUT_POST, 'medico_id', FILTER_VALIDATE_INT);
            $estado = trim($_POST['estado'] ?? '');
            $fechaSolicitud = trim($_POST['fechaSolicitud'] ?? ''); // Readonly, pero lo pasamos
            $fechaRecepcionDocs = trim($_POST['fechaRecepcionDocs'] ?? ''); if(empty($fechaRecepcionDocs)) $fechaRecepcionDocs = null;
            $fechaPago = trim($_POST['fechaPago'] ?? ''); if(empty($fechaPago)) $fechaPago = null;
            $fechaAsignacionMedico = trim($_POST['fechaAsignacionMedico'] ?? ''); if(empty($fechaAsignacionMedico)) $fechaAsignacionMedico = null;
            $fechaVisitaMedico = trim($_POST['fechaVisitaMedico'] ?? ''); if(empty($fechaVisitaMedico)) $fechaVisitaMedico = null;
            $fechaEnvioLG = trim($_POST['fechaEnvioLG'] ?? ''); if(empty($fechaEnvioLG)) $fechaEnvioLG = null;
            $fechaRecepcionLG = trim($_POST['fechaRecepcionLG'] ?? ''); if(empty($fechaRecepcionLG)) $fechaRecepcionLG = null;
            $fechaFinalizacion = trim($_POST['fechaFinalizacion'] ?? ''); if(empty($fechaFinalizacion)) $fechaFinalizacion = null;
            $descripcion = trim($_POST['descripcion'] ?? '');
            $motivo_rechazo = trim($_POST['motivo_rechazo'] ?? '');
            $referencia_pago = trim($_POST['referencia_pago'] ?? '');

            // --- Validaciones (MEJORADAS) ---
            $errors = [];

            // 1. Validar existencia básica de IDs (aunque vengan de hidden, es buena práctica)
            if (empty($socio_id)) $errors[] = "Error interno: ID de socio no proporcionado.";
            if (empty($tipo_servicio_id)) $errors[] = "Error interno: ID de tipo de servicio no proporcionado.";
            if (empty($ejemplar_id)) $errors[] = "Error interno: ID de ejemplar no proporcionado.";

            // 2. Validar que el servicio exista para ese ID (es importante para la seguridad)
            $existingService = Servicio::getById($id);
            if (!$existingService) {
                $_SESSION['error'] = "El servicio que intenta actualizar no fue encontrado.";
                header("Location: index.php?route=servicios_index");
                exit;
            }

            // 3. Validar tipo_servicio_id y requisitos de médico (igual que en create)
            $tipoServicio = TipoServicio::getById($tipo_servicio_id);
            if (!$tipoServicio) {
                $errors[] = "El tipo de servicio asociado no es válido o no existe.";
            } else {
                if (!empty($tipoServicio['requiere_medico']) && empty($medico_id)) {
                    $errors[] = "Este tipo de servicio requiere que se asigne un médico.";
                }
            }

            // 4. Validar medico_id: que exista si fue seleccionado
            if (!empty($medico_id) && !Medico::getById($medico_id)) {
                $errors[] = "El médico seleccionado no es válido o no existe.";
                $medico_id = null;
            }

            // 5. Validar estado y motivo de rechazo
            $posiblesEstados = ['Pendiente Docs/Pago', 'Recibido Completo', 'Pendiente Visita Medico','Pendiente Resultado Lab', 'Enviado a LG', 'Pendiente Respuesta LG', 'Completado', 'Rechazado', 'Cancelado'];
            if (!in_array($estado, $posiblesEstados)) { 
                $errors[] = "El estado seleccionado no es válido."; 
            }
            if ($estado === 'Rechazado' && empty($motivo_rechazo)) { 
                $errors[] = "Debe indicar el motivo del rechazo al seleccionar el estado 'Rechazado'."; 
            }
            // Si el estado no es 'Rechazado', nos aseguramos de que el motivo_rechazo sea NULL en BD
            if ($estado !== 'Rechazado') { $motivo_rechazo = null; }

            // 6. Validar fechas (formato y coherencia)
            $fechas_a_validar = [
                'fechaSolicitud' => 'Fecha de Solicitud',
                'fechaRecepcionDocs' => 'Fecha de Recepción de Documentos',
                'fechaPago' => 'Fecha de Registro de Pago',
                'fechaAsignacionMedico' => 'Fecha de Asignación de Médico',
                'fechaVisitaMedico' => 'Fecha de Visita/Muestras de Médico',
                'fechaEnvioLG' => 'Fecha de Envío a LG',
                'fechaRecepcionLG' => 'Fecha de Recepción de LG',
                'fechaFinalizacion' => 'Fecha de Finalización'
            ];

            foreach ($fechas_a_validar as $campo_fecha => $nombre_campo) {
                if (!empty(${$campo_fecha})) { // Usar variable variable
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', ${$campo_fecha})) {
                        $errors[] = "El formato de la {$nombre_campo} es inválido (debe ser AAAA-MM-DD).";
                    } else {
                        list($y, $m, $d) = explode('-', ${$campo_fecha});
                        if (!checkdate($m, $d, $y)) {
                            $errors[] = "La {$nombre_campo} no es una fecha válida.";
                        } else {
                            $hoy = date('Y-m-d');
                            if (${$campo_fecha} > $hoy) {
                                $errors[] = "La {$nombre_campo} no puede ser futura.";
                            }
                        }
                    }
                }
            }

            // Validar coherencia de fechas (ej. RecepciónDocs no puede ser antes que Solicitud)
            if ($fechaRecepcionDocs && $fechaSolicitud && $fechaRecepcionDocs < $fechaSolicitud) {
                $errors[] = "La Fecha de Recepción de Documentos no puede ser anterior a la Fecha de Solicitud.";
            }
            if ($fechaPago && $fechaRecepcionDocs && $fechaPago < $fechaRecepcionDocs) {
                $errors[] = "La Fecha de Pago no puede ser anterior a la Fecha de Recepción de Documentos.";
            }
            if ($fechaAsignacionMedico && $fechaRecepcionDocs && $fechaAsignacionMedico < $fechaRecepcionDocs) {
                $errors[] = "La Fecha de Asignación de Médico no puede ser anterior a la Fecha de Recepción de Documentos.";
            }
            if ($fechaVisitaMedico && $fechaAsignacionMedico && $fechaVisitaMedico < $fechaAsignacionMedico) {
                $errors[] = "La Fecha de Visita de Médico no puede ser anterior a la Fecha de Asignación de Médico.";
            }
            if ($fechaEnvioLG && $fechaVisitaMedico && $fechaEnvioLG < $fechaVisitaMedico) {
                $errors[] = "La Fecha de Envío a LG no puede ser anterior a la Fecha de Visita de Médico.";
            }
            if ($fechaRecepcionLG && $fechaEnvioLG && $fechaRecepcionLG < $fechaEnvioLG) {
                $errors[] = "La Fecha de Recepción de LG no puede ser anterior a la Fecha de Envío a LG.";
            }
            if ($fechaFinalizacion) { // Si hay fecha de finalización, debe ser después de la solicitud
                if ($fechaSolicitud && $fechaFinalizacion < $fechaSolicitud) {
                    $errors[] = "La Fecha de Finalización no puede ser anterior a la Fecha de Solicitud.";
                }
            } else { // Si el estado es final y la fecha de finalización está vacía, la llenamos con hoy
                if (in_array($estado, ['Completado', 'Rechazado', 'Cancelado'])) {
                    $fechaFinalizacion = date('Y-m-d');
                }
            }


            // 7. Validación de Referencia de Pago (caracteres alfanuméricos y guiones)
            if (!empty($referencia_pago) && !preg_match('/^[A-Za-z0-9\-]+$/', $referencia_pago)) {
                $errors[] = "La referencia de pago contiene caracteres inválidos.";
            }

            // Si hay errores de validación, redirigir
            if (!empty($errors)) {
                $_SESSION['error'] = "Se encontraron los siguientes problemas:<br>" . implode("<br>", $errors);
                header("Location: index.php?route=servicios/edit&id=" . $id); 
                exit;
            }

            // Procesar subida de archivos (solicitud y pago) para edición
            // Estas son opcionales en edición, por eso se usan ifs antes de llamar a handleUpload
            $uploadResultSolicitud = ['status' => 'no_file']; // Default
            if (isset($_FILES['solicitud_file_edit']) && $_FILES['solicitud_file_edit']['error'] === UPLOAD_ERR_OK) {
                $uploadResultSolicitud = Documento::handleUpload('solicitud_file_edit', ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'], 10 * 1024 * 1024, 'servicios' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m'));
            }

            $uploadResultPago = ['status' => 'no_file']; // Default
            if (isset($_FILES['pago_file_edit']) && $_FILES['pago_file_edit']['error'] === UPLOAD_ERR_OK) {
                $uploadResultPago = Documento::handleUpload('pago_file_edit', ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'], 10 * 1024 * 1024, 'servicios' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m'));
            }

            $fileErrors = [];
            $solicitudDocData = null;
            $pagoDocData = null;

            if ($uploadResultSolicitud['status'] === 'error') {
                $fileErrors[] = "Error con la Solicitud de Servicio (archivo): " . ($uploadResultSolicitud['message'] ?? 'Desconocido');
            } elseif ($uploadResultSolicitud['status'] === 'success') {
                $solicitudDocData = $uploadResultSolicitud['data'];
            }

            if ($uploadResultPago['status'] === 'error') {
                $fileErrors[] = "Error con el Comprobante de Pago (archivo): " . ($uploadResultPago['message'] ?? 'Desconocido');
            } elseif ($uploadResultPago['status'] === 'success') {
                $pagoDocData = $uploadResultPago['data'];
            }

            if (!empty($fileErrors)) {
                // Si hay errores de subida de archivo, mostramos y volvemos al formulario.
                // Es crucial limpiar los archivos subidos exitosamente para evitar huérfanos.
                if ($uploadResultSolicitud['status'] === 'success' && file_exists(UPLOADS_BASE_DIR . $solicitudDocData['savedPath'])) {
                    unlink(UPLOADS_BASE_DIR . $solicitudDocData['savedPath']);
                    error_log("INFO: Archivo de solicitud limpiado debido a error de subida de otro archivo en update.");
                }
                if ($uploadResultPago['status'] === 'success' && file_exists(UPLOADS_BASE_DIR . $pagoDocData['savedPath'])) {
                    unlink(UPLOADS_BASE_DIR . $pagoDocData['savedPath']);
                    error_log("INFO: Archivo de pago limpiado debido a error de subida de otro archivo en update.");
                }
                $_SESSION['error'] = "Se encontraron problemas al subir nuevos documentos:<br>" . implode("<br>", $fileErrors);
                header("Location: index.php?route=servicios/edit&id=" . $id);
                exit;
            }


            // Preparar array $data para actualizar el servicio
            $data = [
                'ejemplar_id' => $ejemplar_id ?: null, 
                'medico_id' => $medico_id ?: null, 
                'estado' => $estado,
                'fechaSolicitud' => $fechaSolicitud, 
                'fechaRecepcionDocs' => $fechaRecepcionDocs, 
                'fechaPago' => $fechaPago,
                'fechaAsignacionMedico' => $fechaAsignacionMedico, 
                'fechaVisitaMedico' => $fechaVisitaMedico,
                'fechaEnvioLG' => $fechaEnvioLG, 
                'fechaRecepcionLG' => $fechaRecepcionLG,
                'fechaFinalizacion' => $fechaFinalizacion, 
                'descripcion' => $descripcion ?: null,
                'motivo_rechazo' => $motivo_rechazo, 
                'referencia_pago' => $referencia_pago ?: null,
                'id_usuario_ultima_mod' => $userId
            ];
             
            if (Servicio::update($id, $data)) { // Actualizar datos del servicio
                $_SESSION['message'] = "Servicio #" . $id . " actualizado.";
                unset($_SESSION['form_data']); // Limpiar datos del formulario en sesión

                // Si se subieron nuevos documentos, registrarlos en la DB
                if ($solicitudDocData) {
                    Documento::store([
                        'tipoDocumento' => 'SOLICITUD_SERVICIO',
                        'nombreArchivoOriginal' => $solicitudDocData['originalName'],
                        'rutaArchivo' => $solicitudDocData['savedPath'],
                        'mimeType' => $solicitudDocData['mimeType'],
                        'sizeBytes' => $solicitudDocData['size'],
                        'socio_id' => null, 'ejemplar_id' => null,
                        'servicio_id' => $id, // ID del servicio actual
                        'id_usuario' => $userId,
                        'comentarios' => 'Solicitud de servicio (actualizada).'
                    ]);
                }
                if ($pagoDocData) {
                    Documento::store([
                        'tipoDocumento' => 'COMPROBANTE_PAGO',
                        'nombreArchivoOriginal' => $pagoDocData['originalName'],
                        'rutaArchivo' => $pagoDocData['savedPath'],
                        'mimeType' => $pagoDocData['mimeType'],
                        'sizeBytes' => $pagoDocData['size'],
                        'socio_id' => null, 'ejemplar_id' => null,
                        'servicio_id' => $id,
                        'id_usuario' => $userId,
                        'comentarios' => 'Comprobante de pago (actualizado).'
                    ]);
                }
                // Añadir aquí procesamiento para otros campos de archivo que añadas al edit form
                
                header("Location: index.php?route=servicios_index");
                exit;

            } else {
                // Si falla el guardado en DB, limpiamos los archivos que ya se subieron
                if ($uploadResultSolicitud['status'] === 'success' && file_exists(UPLOADS_BASE_DIR . $solicitudDocData['savedPath'])) {
                    unlink(UPLOADS_BASE_DIR . $solicitudDocData['savedPath']);
                    error_log("ERROR: Archivo de solicitud limpiado tras fallo en DB en update: " . $solicitudDocData['savedPath']);
                }
                if ($uploadResultPago['status'] === 'success' && file_exists(UPLOADS_BASE_DIR . $pagoDocData['savedPath'])) {
                    unlink(UPLOADS_BASE_DIR . $pagoDocData['savedPath']);
                    error_log("ERROR: Archivo de pago limpiado tras fallo en DB en update: " . $pagoDocData['savedPath']);
                }

                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al actualizar el servicio. Verifique los datos o intente más tarde.'; 
                unset($_SESSION['error_details']);
                $_SESSION['error'] = "Error al actualizar el servicio: " . $error_detail;
                header("Location: index.php?route=servicios/edit&id=" . $id); 
                exit; 
            }

        } else { 
            $_SESSION['error'] = "No se recibieron datos del formulario.";
            header("Location: index.php?route=servicios/edit&id=" . $id); 
            exit; 
        }
   }

    /**
     * Cambia el estado de un servicio a 'Cancelado'.
     */
    public function cancel($id = null) {
         check_permission();
         $servicioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
         if ($servicioId) {
             if (Servicio::cancel($servicioId, $_SESSION['user']['id_usuario'])) {
                 $_SESSION['message'] = "Servicio #" . $servicioId . " cancelado exitosamente.";
             } else {
                  $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al cancelar el servicio.';
                  unset($_SESSION['error_details']);
                  $_SESSION['error'] = "Error al cancelar el servicio: " . $error_detail;
             }
         } else { 
             $_SESSION['error'] = "ID de servicio inválido.";
         }
         header("Location: index.php?route=servicios_index");
         exit;
    }

}