<?php
// app/controllers/ServiciosController.php
require_once __DIR__ . '/../models/Servicio.php';
require_once __DIR__ . '/../models/TipoServicio.php';
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Ejemplar.php';
require_once __DIR__ . '/../models/Medico.php';
require_once __DIR__ . '/../models/Documento.php'; // Incluir modelo Documento

class ServiciosController {

    // Helper para verificar sesión
    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user'])) { header("Location: index.php?route=login"); exit; }
    }

    // Helper para procesar subida de un documento específico para un servicio
    private function handleServicioDocumentUpload($fileInputName, $servicioId, $tipoDocumento, $userId) {
        // Permitir tipos comunes de documentos e imágenes
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxFileSize = 10 * 1024 * 1024; // 10 MB (ajustar si es necesario)

        // Verificar si el archivo fue subido y es válido
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            // Usar subcarpeta 'servicios' y organizar por año/mes
            $subfolder = 'servicios' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m');
            $uploadResult = Documento::handleUpload($fileInputName, $allowedTypes, $maxFileSize, $subfolder);

            if ($uploadResult['status'] === 'success') {
                $docData = [
                    'tipoDocumento' => $tipoDocumento,
                    'nombreArchivoOriginal' => $uploadResult['data']['originalName'],
                    'rutaArchivo' => $uploadResult['data']['savedPath'], // Ruta relativa guardada por handleUpload
                    'mimeType' => $uploadResult['data']['mimeType'],
                    'sizeBytes' => $uploadResult['data']['size'],
                    'socio_id' => null,
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
         // Si no se subió archivo (UPLOAD_ERR_NO_FILE), no hacemos nada y retornamos false
         return false;
    }


    /**
     * Muestra la lista de servicios
     */
    public function index() {
        $this->checkSession();
        $filters = [];
        if (!empty($_GET['filtro_estado'])) $filters['estado'] = $_GET['filtro_estado'];
        if (!empty($_GET['filtro_socio_id'])) $filters['socio_id'] = filter_input(INPUT_GET, 'filtro_socio_id', FILTER_VALIDATE_INT);
        if (!empty($_GET['filtro_tipo_id'])) $filters['tipo_servicio_id'] = filter_input(INPUT_GET, 'filtro_tipo_id', FILTER_VALIDATE_INT);


        $servicios = Servicio::getAll($filters); // <-- Obtiene los servicios del modelo

       

        // El resto del código sigue igual
        $sociosList = Socio::getActiveSociosForSelect(); // Para el filtro
        $tiposServicioList = TipoServicio::getActiveForSelect(); // Para el filtro

        $pageTitle = 'Listado de Servicios';
        $currentRoute = 'servicios_index';
        $contentView = __DIR__ . '/../views/servicios/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Muestra el formulario para registrar un nuevo servicio
     */
    public function create() {
        $this->checkSession();
        $tiposServicioList = TipoServicio::getActiveForSelect();
        $sociosList = Socio::getActiveSociosForSelect();
        $ejemplares = Ejemplar::getAll(); // Cargar todos para filtrar con JS
        $medicosList = Medico::getActiveMedicosForSelect();

         // Preparar datos de tipos de servicio para JS (requiere médico)
         $tiposServicioDataJS = [];
         if (!empty($tiposServicioList)) {
              $allTiposData = TipoServicio::getAll(); // Necesitamos info completa
              foreach($allTiposData as $tipo) {
                  if (isset($tiposServicioList[$tipo['id_tipo_servicio']])) { // Solo incluir activos
                     $tiposServicioDataJS[$tipo['id_tipo_servicio']] = [
                         'req_medico' => !empty($tipo['requiere_medico']) // Convertir a booleano JS
                     ];
                  }
              }
         }


        if (empty($sociosList)) $_SESSION['warning'] = "No hay socios activos registrados.";
        if (empty($tiposServicioList)) $_SESSION['warning'] = "No hay tipos de servicio activos.";

        $pageTitle = 'Registrar Nuevo Servicio';
        $currentRoute = 'servicios/create';
        $contentView = __DIR__ . '/../views/servicios/create.php';
        require_once __DIR__ . '/../views/layouts/master.php'; // Master extraerá las listas
    }

    /**
     * Guarda la nueva solicitud de servicio Y los documentos iniciales.
     */
    public function store() {
        $this->checkSession();
        $userId = $_SESSION['user']['id_usuario'];

        if (isset($_POST)) {
            // Recoger datos del servicio
            $socio_id = filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT);
            $tipo_servicio_id = filter_input(INPUT_POST, 'tipo_servicio_id', FILTER_VALIDATE_INT);
            $ejemplar_id = filter_input(INPUT_POST, 'ejemplar_id', FILTER_VALIDATE_INT);
            $medico_id = filter_input(INPUT_POST, 'medico_id', FILTER_VALIDATE_INT);
            $fechaSolicitud = trim($_POST['fechaSolicitud'] ?? date('Y-m-d'));
            $descripcion = trim($_POST['descripcion'] ?? '');
            $referencia_pago = trim($_POST['referencia_pago'] ?? '');

            // --- Validaciones ---
            $errors = [];
            if (empty($socio_id)) $errors[] = "Debe seleccionar un socio.";
            if (empty($tipo_servicio_id)) $errors[] = "Debe seleccionar un tipo de servicio.";
            if (empty($fechaSolicitud)) $errors[] = "La fecha de solicitud es obligatoria.";
            if (empty($ejemplar_id)) { $errors[] = "Debe seleccionar un ejemplar."; }
            else {
                 $ejemplarData = Ejemplar::getById($ejemplar_id);
                 if (!$ejemplarData || $ejemplarData['socio_id'] != $socio_id) {
                      $errors[] = "El ejemplar seleccionado no pertenece al socio elegido.";
                 }
            }

            // Validar si los archivos obligatorios fueron subidos correctamente
             $solicitudOk = isset($_FILES['solicitud_file']) && $_FILES['solicitud_file']['error'] === UPLOAD_ERR_OK;
             $pagoOk = isset($_FILES['pago_file']) && $_FILES['pago_file']['error'] === UPLOAD_ERR_OK;

             if (!$solicitudOk) {
                  $errors[] = "Debe adjuntar la Solicitud de Servicio (Error: " . ($_FILES['solicitud_file']['error'] ?? 'No subido') . ").";
             }
             if (!$pagoOk) {
                  $errors[] = "Debe adjuntar el Comprobante de Pago (Error: " . ($_FILES['pago_file']['error'] ?? 'No subido') . ").";
             }
             // ... (Otras validaciones) ...

            if (!empty($errors)) {
                $_SESSION['error'] = implode("<br>", $errors);
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?route=servicios/create");
                exit;
            }

            // Preparar datos para el modelo Servicio
            $data = [ /* ... datos ... */
                 'socio_id' => $socio_id, 'ejemplar_id' => $ejemplar_id,
                 'tipo_servicio_id' => $tipo_servicio_id, 'medico_id' => $medico_id ?: null,
                 'estado' => 'Recibido Completo', // Estado inicial ahora que pedimos docs
                 'fechaSolicitud' => $fechaSolicitud, 'descripcion' => $descripcion ?: null,
                 'referencia_pago' => $referencia_pago ?: null,
                 'id_usuario_registro' => $userId, 'id_usuario_ultima_mod' => $userId,
                  'fechaRecepcionDocs' => date('Y-m-d'), // Marcar fecha recepción docs hoy
                  'fechaPago' => date('Y-m-d'), // Marcar fecha pago hoy (o tomar de form si se añade)
                  'fechaAsignacionMedico' => ($medico_id ? date('Y-m-d') : null),
                 // ... resto a NULL ...
            ];

            $servicioId = Servicio::store($data); // Guardar datos del servicio

            if ($servicioId !== false) {
                $_SESSION['message'] = "Servicio registrado con ID: " . $servicioId . ".";
                unset($_SESSION['form_data']);

                // --- Procesar Documentos Subidos (Solicitud y Pago) ---
                // Ya validamos que los archivos venían OK, ahora los procesamos
                $this->handleServicioDocumentUpload('solicitud_file', $servicioId, 'SOLICITUD_SERVICIO', $userId);
                $this->handleServicioDocumentUpload('pago_file', $servicioId, 'COMPROBANTE_PAGO', $userId);
                // Los warnings de subida (si fallara algo aquí inesperadamente) se añadirían a la sesión

            } else {
                $error_detail = $_SESSION['error_details'] ?? 'Verifique los datos ingresados.';
                unset($_SESSION['error_details']);
                $_SESSION['error'] = "Error al registrar el servicio. " . $error_detail;
                $_SESSION['form_data'] = $_POST;
                // ¡Importante! No redirigir aquí si la subida falló, el usuario perdería los archivos.
                // Mejor mostrar el error en la misma página de creación.
                 $tiposServicioList = TipoServicio::getActiveForSelect();
                 $sociosList = Socio::getActiveSociosForSelect();
                 $ejemplares = Ejemplar::getAll();
                 $medicosList = Medico::getActiveMedicosForSelect();
                 $tiposServicioDataJS = []; // Recalcular para la vista
                 if (!empty($tiposServicioList)) { $allTiposData = TipoServicio::getAll(); foreach($allTiposData as $tipo) { if(isset($tiposServicioList[$tipo['id_tipo_servicio']])) {$tiposServicioDataJS[$tipo['id_tipo_servicio']] = ['req_medico' => !empty($tipo['requiere_medico'])]; } } }
                 $pageTitle = 'Registrar Nuevo Servicio';
                 $currentRoute = 'servicios/create';
                 $contentView = __DIR__ . '/../views/servicios/create.php';
                 require_once __DIR__ . '/../views/layouts/master.php';
                 exit; // Detener aquí
            }
        } else {
             $_SESSION['error'] = "No se recibieron datos del formulario.";
        }
        // Redirigir solo si todo fue exitoso (incluyendo subidas)
        header("Location: index.php?route=servicios_index");
        exit;
    }

    /**
     * Muestra el formulario para editar/actualizar un servicio y sus documentos.
     */
    public function edit($id = null) {
        $this->checkSession();
        $servicioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$servicioId) { $_SESSION['error'] = "ID inválido."; header("Location: index.php?route=servicios_index"); exit; }

        $servicio = Servicio::getById($servicioId);
        if (!$servicio) { $_SESSION['error'] = "Servicio no encontrado."; header("Location: index.php?route=servicios_index"); exit; }

        // Cargar listas y datos necesarios para la vista
        $tiposServicioList = TipoServicio::getActiveForSelect();
        $sociosList = Socio::getActiveSociosForSelect();
        $ejemplares = Ejemplar::getAll();
        $medicosList = Medico::getActiveMedicosForSelect();
        $posiblesEstados = ['Pendiente Docs/Pago', 'Recibido Completo', 'Pendiente Visita Medico','Pendiente Resultado Lab', 'Enviado a LG', 'Pendiente Respuesta LG', 'Completado', 'Rechazado', 'Cancelado'];

        // Obtener Documentos asociados a ESTE servicio
        $documentosServicio = Documento::getByEntityId('servicio', $servicioId);

        $pageTitle = 'Editar/Ver Servicio #' . $servicio['id_servicio'];
        $currentRoute = 'servicios/edit';
        $contentView = __DIR__ . '/../views/servicios/edit.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Actualiza un servicio existente y maneja subida de nuevos documentos.
     */
    public function update() {
        $this->checkSession();
        $userId = $_SESSION['user']['id_usuario'];

        if (isset($_POST['id_servicio'])) {
            $id = filter_input(INPUT_POST, 'id_servicio', FILTER_VALIDATE_INT);
            if (!$id) {
                $_SESSION['error'] = "ID de servicio inválido.";
                header("Location: index.php?route=servicios_index");
                exit;
            }

            // Recoger TODOS los campos del formulario de edición
            $ejemplar_id = filter_input(INPUT_POST, 'ejemplar_id', FILTER_VALIDATE_INT);
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

            // --- Validaciones ---
             $errors = [];
             $posiblesEstados = ['Pendiente Docs/Pago', 'Recibido Completo', 'Pendiente Visita Medico','Pendiente Resultado Lab', 'Enviado a LG', 'Pendiente Respuesta LG', 'Completado', 'Rechazado', 'Cancelado'];
             if (!in_array($estado, $posiblesEstados)) { $errors[] = "Estado inválido."; }
             // ... validar fechas ...
              if ($estado === 'Rechazado' && empty($motivo_rechazo)) { $errors[] = "Debe indicar motivo de rechazo."; }
              if ($estado !== 'Rechazado') { $motivo_rechazo = null; }
              if (($estado === 'Completado' || $estado === 'Rechazado' || $estado === 'Cancelado') && empty($fechaFinalizacion)) {
                  $fechaFinalizacion = date('Y-m-d');
              }
             // ... más validaciones ...

             if (!empty($errors)) {
                  $_SESSION['error'] = implode("<br>", $errors);
                  header("Location: index.php?route=servicios/edit&id=" . $id);
                  exit;
             }

             // Preparar array $data
             $data = [
                  'ejemplar_id' => $ejemplar_id ?: null, 'medico_id' => $medico_id ?: null, 'estado' => $estado,
                  'fechaSolicitud' => $fechaSolicitud, 'fechaRecepcionDocs' => $fechaRecepcionDocs, 'fechaPago' => $fechaPago,
                  'fechaAsignacionMedico' => $fechaAsignacionMedico, 'fechaVisitaMedico' => $fechaVisitaMedico,
                  'fechaEnvioLG' => $fechaEnvioLG, 'fechaRecepcionLG' => $fechaRecepcionLG,
                  'fechaFinalizacion' => $fechaFinalizacion, 'descripcion' => $descripcion ?: null,
                  'motivo_rechazo' => $motivo_rechazo, 'referencia_pago' => $referencia_pago ?: null,
                  'id_usuario_ultima_mod' => $userId
             ];

             if (Servicio::update($id, $data)) { // Actualizar datos del servicio
                 $_SESSION['message'] = "Servicio #" . $id . " actualizado.";

                 // Procesar NUEVOS Documentos Subidos
                 $this->handleServicioDocumentUpload('solicitud_file_edit', $id, 'SOLICITUD_SERVICIO', $userId);
                 $this->handleServicioDocumentUpload('pago_file_edit', $id, 'COMPROBANTE_PAGO', $userId);
                 // Añadir aquí procesamiento para otros campos de archivo que añadas al edit form

                 // --- ¡CAMBIO EN LA REDIRECCIÓN! ---
                 header("Location: index.php?route=servicios_index"); // Redirigir al LISTADO
                 exit;
                 // --- FIN DEL CAMBIO ---

             } else {
                  $error_detail = $_SESSION['error_details'] ?? 'Error desconocido.'; unset($_SESSION['error_details']);
                  $_SESSION['error'] = "Error al actualizar el servicio. " . $error_detail;
                  header("Location: index.php?route=servicios/edit&id=" . $id); exit; // Volver a edit en error
             }

        } else {
            $_SESSION['error'] = "Falta ID del servicio.";
            header("Location: index.php?route=servicios_index"); exit; // Ir a índice si falta ID
        }
        
   }

    /**
     * Cambia el estado de un servicio a 'Cancelado'.
     */
    public function cancel($id = null) {
         $this->checkSession();
         $servicioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
         if ($servicioId) {
             if (Servicio::cancel($servicioId, $_SESSION['user']['id_usuario'])) {
                 $_SESSION['message'] = "Servicio #" . $servicioId . " cancelado exitosamente.";
             } else {
                  $error_detail = $_SESSION['error_details'] ?? 'Error desconocido.';
                  unset($_SESSION['error_details']);
                  $_SESSION['error'] = "Error al cancelar el servicio. " . $error_detail;
             }
         } else { $_SESSION['error'] = "ID de servicio inválido."; }
         header("Location: index.php?route=servicios_index");
         exit;
    }

} // Fin clase ServiciosController
?>