<?php
// app/controllers/ServiciosController.php
require_once __DIR__ . '/../models/Servicio.php';
require_once __DIR__ . '/../models/TipoServicio.php';
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Ejemplar.php';
require_once __DIR__ . '/../models/Medico.php';
require_once __DIR__ . '/../models/Documento.php';

class ServiciosController {

    // CORREGIDO: Se elimina el helper checkSession()
    
    private function handleServicioDocumentUpload($fileInputName, $servicioId, $tipoDocumento, $userId) {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxFileSize = 10 * 1024 * 1024;

        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            $subfolder = 'servicios' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m');
            $uploadResult = Documento::handleUpload($fileInputName, $allowedTypes, $maxFileSize, $subfolder);
            
            if ($uploadResult['status'] === 'success') {
                $docData = [
                    'tipoDocumento' => $tipoDocumento,
                    'nombreArchivoOriginal' => $uploadResult['data']['originalName'],
                    'rutaArchivo' => $uploadResult['data']['savedPath'],
                    'mimeType' => $uploadResult['data']['mimeType'],
                    'sizeBytes' => $uploadResult['data']['size'],
                    'socio_id' => null,
                    'ejemplar_id' => null,
                    'servicio_id' => $servicioId,
                    'id_usuario' => $userId,
                    'comentarios' => 'Documento de servicio.'
                ];
                if (!Documento::store($docData)) {
                     error_log("Error BD al guardar doc {$tipoDocumento} para servicio {$servicioId}.");
                     $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error DB doc: " . htmlspecialchars($uploadResult['data']['originalName']) . ". ";
                }
                 return true;
            } else {
                 $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error al subir " . htmlspecialchars($_FILES[$fileInputName]['name']) . ": " . $uploadResult['message'] . ". ";
            }
        } elseif (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_NO_FILE) {
             $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error PHP al subir " . $fileInputName . " (Code: " . $_FILES[$fileInputName]['error'] . "). ";
        }
         return false;
    }

    public function index() {
        check_permission();
        
        $filters = [];
        if (!empty($_GET['filtro_estado'])) $filters['estado'] = $_GET['filtro_estado'];
        if (!empty($_GET['filtro_socio_id'])) $filters['socio_id'] = filter_input(INPUT_GET, 'filtro_socio_id', FILTER_VALIDATE_INT);
        if (!empty($_GET['filtro_tipo_id'])) $filters['tipo_servicio_id'] = filter_input(INPUT_GET, 'filtro_tipo_id', FILTER_VALIDATE_INT);

        $servicios = Servicio::getAll($filters);
        $sociosList = Socio::getActiveSociosForSelect();
        $tiposServicioList = TipoServicio::getActiveForSelect();

        $pageTitle = 'Listado de Servicios';
        $currentRoute = 'servicios_index';
        $contentView = __DIR__ . '/../views/servicios/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function create() {
        check_permission();

        $tiposServicioList = TipoServicio::getActiveForSelect();
        $sociosList = Socio::getActiveSociosForSelect();
        $ejemplares = Ejemplar::getAll();
        $medicosList = Medico::getActiveMedicosForSelect();
        
        $tiposServicioDataJS = [];
        if (!empty($tiposServicioList)) {
              $allTiposData = TipoServicio::getAll();
              foreach($allTiposData as $tipo) {
                  if (isset($tiposServicioList[$tipo['id_tipo_servicio']])) {
                     $tiposServicioDataJS[$tipo['id_tipo_servicio']] = [
                         'req_medico' => !empty($tipo['requiere_medico'])
                     ];
                  }
              }
         }

        if (empty($sociosList)) $_SESSION['warning'] = "No hay socios activos registrados.";
        if (empty($tiposServicioList)) $_SESSION['warning'] = "No hay tipos de servicio activos.";

        $pageTitle = 'Registrar Nuevo Servicio';
        $currentRoute = 'servicios/create';
        $contentView = __DIR__ . '/../views/servicios/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function store() {
        check_permission();
        $userId = $_SESSION['user']['id_usuario'];
        
        if (isset($_POST)) {
            $socio_id = filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT);
            $tipo_servicio_id = filter_input(INPUT_POST, 'tipo_servicio_id', FILTER_VALIDATE_INT);
            $ejemplar_id = filter_input(INPUT_POST, 'ejemplar_id', FILTER_VALIDATE_INT);
            $medico_id = filter_input(INPUT_POST, 'medico_id', FILTER_VALIDATE_INT);
            $fechaSolicitud = trim($_POST['fechaSolicitud'] ?? date('Y-m-d'));
            $descripcion = trim($_POST['descripcion'] ?? '');
            $referencia_pago = trim($_POST['referencia_pago'] ?? '');
            
            $errors = [];
            if (empty($socio_id)) $errors[] = "Debe seleccionar un socio.";
            if (empty($tipo_servicio_id)) $errors[] = "Debe seleccionar un tipo de servicio.";
            if (empty($fechaSolicitud)) $errors[] = "La fecha de solicitud es obligatoria.";
            if (empty($ejemplar_id)) {
                $errors[] = "Debe seleccionar un ejemplar.";
            } else {
                 $ejemplarData = Ejemplar::getById($ejemplar_id);
                 if (!$ejemplarData || $ejemplarData['socio_id'] != $socio_id) {
                      $errors[] = "El ejemplar seleccionado no pertenece al socio elegido.";
                 }
            }
             $solicitudOk = isset($_FILES['solicitud_file']) && $_FILES['solicitud_file']['error'] === UPLOAD_ERR_OK;
             $pagoOk = isset($_FILES['pago_file']) && $_FILES['pago_file']['error'] === UPLOAD_ERR_OK;

             if (!$solicitudOk) {
                  $errors[] = "Debe adjuntar la Solicitud de Servicio (Error: " . ($_FILES['solicitud_file']['error'] ?? 'No subido') . ").";
             }
             if (!$pagoOk) {
                  $errors[] = "Debe adjuntar el Comprobante de Pago (Error: " . ($_FILES['pago_file']['error'] ?? 'No subido') . ").";
             }

            if (!empty($errors)) {
                $_SESSION['error'] = implode("<br>", $errors);
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?route=servicios/create");
                exit;
            }

            $data = [
                 'socio_id' => $socio_id, 'ejemplar_id' => $ejemplar_id,
                 'tipo_servicio_id' => $tipo_servicio_id, 'medico_id' => $medico_id ?: null,
                 'estado' => 'Recibido Completo',
                 'fechaSolicitud' => $fechaSolicitud, 'descripcion' => $descripcion ?: null,
                 'referencia_pago' => $referencia_pago ?: null,
                 'id_usuario_registro' => $userId, 'id_usuario_ultima_mod' => $userId,
                 'fechaRecepcionDocs' => date('Y-m-d'),
                 'fechaPago' => date('Y-m-d'),
                 'fechaAsignacionMedico' => ($medico_id ? date('Y-m-d') : null),
            ];
            $servicioId = Servicio::store($data);

            if ($servicioId !== false) {
                $_SESSION['message'] = "Servicio registrado con ID: " . $servicioId . ".";
                unset($_SESSION['form_data']);

                $this->handleServicioDocumentUpload('solicitud_file', $servicioId, 'SOLICITUD_SERVICIO', $userId);
                $this->handleServicioDocumentUpload('pago_file', $servicioId, 'COMPROBANTE_PAGO', $userId);
            } else {
                $error_detail = $_SESSION['error_details'] ?? 'Verifique los datos ingresados.';
                unset($_SESSION['error_details']);
                $_SESSION['error'] = "Error al registrar el servicio. " . $error_detail;
                $_SESSION['form_data'] = $_POST;
                
                 $tiposServicioList = TipoServicio::getActiveForSelect();
                 $sociosList = Socio::getActiveSociosForSelect();
                 $ejemplares = Ejemplar::getAll();
                 $medicosList = Medico::getActiveMedicosForSelect();
                 $tiposServicioDataJS = [];
                 if (!empty($tiposServicioList)) { $allTiposData = TipoServicio::getAll(); foreach($allTiposData as $tipo) { if(isset($tiposServicioList[$tipo['id_tipo_servicio']])) {$tiposServicioDataJS[$tipo['id_tipo_servicio']] = ['req_medico' => !empty($tipo['requiere_medico'])]; } } }
                 $pageTitle = 'Registrar Nuevo Servicio';
                 $currentRoute = 'servicios/create';
                 $contentView = __DIR__ . '/../views/servicios/create.php';
                 require_once __DIR__ . '/../views/layouts/master.php';
                 exit;
            }
        } else {
             $_SESSION['error'] = "No se recibieron datos del formulario.";
        }
        
        header("Location: index.php?route=servicios_index");
        exit;
    }

    public function edit($id = null) {
        check_permission();

        $servicioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$servicioId) { $_SESSION['error'] = "ID inválido."; header("Location: index.php?route=servicios_index"); exit; }

        $servicio = Servicio::getById($servicioId);
        if (!$servicio) { $_SESSION['error'] = "Servicio no encontrado."; header("Location: index.php?route=servicios_index"); exit; }

        $tiposServicioList = TipoServicio::getActiveForSelect();
        $sociosList = Socio::getActiveSociosForSelect();
        $ejemplares = Ejemplar::getAll();
        $medicosList = Medico::getActiveMedicosForSelect();
        $posiblesEstados = ['Pendiente Docs/Pago', 'Recibido Completo', 'Pendiente Visita Medico','Pendiente Resultado Lab', 'Enviado a LG', 'Pendiente Respuesta LG', 'Completado', 'Rechazado', 'Cancelado'];
        $documentosServicio = Documento::getByEntityId('servicio', $servicioId);
        
        $pageTitle = 'Editar/Ver Servicio #' . $servicio['id_servicio'];
        $currentRoute = 'servicios/edit';
        $contentView = __DIR__ . '/../views/servicios/edit.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function update() {
        check_permission();
        $userId = $_SESSION['user']['id_usuario'];
        
        if (isset($_POST['id_servicio'])) {
            $id = filter_input(INPUT_POST, 'id_servicio', FILTER_VALIDATE_INT);
            if (!$id) {
                $_SESSION['error'] = "ID de servicio inválido.";
                header("Location: index.php?route=servicios_index");
                exit;
            }

            $ejemplar_id = filter_input(INPUT_POST, 'ejemplar_id', FILTER_VALIDATE_INT);
            $medico_id = filter_input(INPUT_POST, 'medico_id', FILTER_VALIDATE_INT);
            $estado = trim($_POST['estado'] ?? '');
            $fechaSolicitud = trim($_POST['fechaSolicitud'] ?? '');
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
            
            $errors = [];
            $posiblesEstados = ['Pendiente Docs/Pago', 'Recibido Completo', 'Pendiente Visita Medico','Pendiente Resultado Lab', 'Enviado a LG', 'Pendiente Respuesta LG', 'Completado', 'Rechazado', 'Cancelado'];
            if (!in_array($estado, $posiblesEstados)) { $errors[] = "Estado inválido."; }
            if ($estado === 'Rechazado' && empty($motivo_rechazo)) { $errors[] = "Debe indicar motivo de rechazo."; }
            if ($estado !== 'Rechazado') { $motivo_rechazo = null; }
            if (($estado === 'Completado' || $estado === 'Rechazado' || $estado === 'Cancelado') && empty($fechaFinalizacion)) {
                $fechaFinalizacion = date('Y-m-d');
            }

             if (!empty($errors)) {
                  $_SESSION['error'] = implode("<br>", $errors);
                  header("Location: index.php?route=servicios/edit&id=" . $id);
                  exit;
             }

             $data = [
                  'ejemplar_id' => $ejemplar_id ?: null, 'medico_id' => $medico_id ?: null, 'estado' => $estado,
                  'fechaSolicitud' => $fechaSolicitud, 'fechaRecepcionDocs' => $fechaRecepcionDocs, 'fechaPago' => $fechaPago,
                  'fechaAsignacionMedico' => $fechaAsignacionMedico, 'fechaVisitaMedico' => $fechaVisitaMedico,
                  'fechaEnvioLG' => $fechaEnvioLG, 'fechaRecepcionLG' => $fechaRecepcionLG,
                  'fechaFinalizacion' => $fechaFinalizacion, 'descripcion' => $descripcion ?: null,
                  'motivo_rechazo' => $motivo_rechazo, 'referencia_pago' => $referencia_pago ?: null,
                  'id_usuario_ultima_mod' => $userId
             ];
             
             if (Servicio::update($id, $data)) {
                 $_SESSION['message'] = "Servicio #" . $id . " actualizado.";
                 $this->handleServicioDocumentUpload('solicitud_file_edit', $id, 'SOLICITUD_SERVICIO', $userId);
                 $this->handleServicioDocumentUpload('pago_file_edit', $id, 'COMPROBANTE_PAGO', $userId);
                 
                 header("Location: index.php?route=servicios_index");
                 exit;
             } else {
                  $error_detail = $_SESSION['error_details'] ?? 'Error desconocido.'; unset($_SESSION['error_details']);
                  $_SESSION['error'] = "Error al actualizar el servicio. " . $error_detail;
                  header("Location: index.php?route=servicios/edit&id=" . $id); exit;
             }

        } else {
            $_SESSION['error'] = "Falta ID del servicio.";
            header("Location: index.php?route=servicios_index"); exit;
        }
   }

    public function cancel($id = null) {
        check_permission();
        
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
}