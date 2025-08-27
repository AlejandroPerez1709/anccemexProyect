<?php
// app/controllers/ServiciosController.php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once __DIR__ . '/../models/Servicio.php';
require_once __DIR__ . '/../models/TipoServicio.php';
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Ejemplar.php';
require_once __DIR__ . '/../models/Medico.php';
require_once __DIR__ . '/../models/Documento.php';
require_once __DIR__ . '/../models/ServicioHistorial.php';
require_once __DIR__ . '/../models/Auditoria.php';
require_once __DIR__ . '/../../config/config.php';

class ServiciosController {

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
        }
        return false;
    }

    public function index() {
        check_permission();
        // --- INICIO DE LÓGICA DE FILTROS AVANZADOS ---
        $filters = [];
        $estadoSeleccionado = $_GET['filtro_estado'] ?? 'en_proceso';

        if ($estadoSeleccionado === 'en_proceso') {
            $filters['estado_not_in'] = ['Completado', 'Rechazado', 'Cancelado'];
        } elseif (!empty($estadoSeleccionado)) {
            $filters['estado'] = $estadoSeleccionado;
        }
        
        if (!empty($_GET['filtro_socio_id'])) $filters['socio_id'] = filter_input(INPUT_GET, 'filtro_socio_id', FILTER_VALIDATE_INT);
        if (!empty($_GET['filtro_tipo_id'])) $filters['tipo_servicio_id'] = filter_input(INPUT_GET, 'filtro_tipo_id', FILTER_VALIDATE_INT);
        // NUEVOS FILTROS
        if (!empty($_GET['filtro_medico_id'])) $filters['medico_id'] = filter_input(INPUT_GET, 'filtro_medico_id', FILTER_VALIDATE_INT);
        if (!empty($_GET['filtro_fecha_inicio'])) $filters['fecha_inicio'] = $_GET['filtro_fecha_inicio'];
        if (!empty($_GET['filtro_fecha_fin'])) $filters['fecha_fin'] = $_GET['filtro_fecha_fin'];
        // --- FIN DE LÓGICA DE FILTROS AVANZADOS ---
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $records_per_page = 15;
        $offset = ($page - 1) * $records_per_page;

        $total_records = Servicio::countAll($filters);
        $total_pages = ceil($total_records / $records_per_page);

        $servicios = Servicio::getAll($filters, $records_per_page, $offset);
        foreach ($servicios as $key => $servicio) {
            $servicios[$key]['document_status'] = Documento::getDocumentStatusForServicio($servicio['id_servicio']);
        }

        $sociosList = Socio::getActiveSociosForSelect();
        $tiposServicioList = TipoServicio::getActiveForSelect();
        $medicosList = Medico::getActiveMedicosForSelect(); // <-- SE OBTIENE LA LISTA DE MÉDICOS
        $posiblesEstados = ['Pendiente Docs/Pago', 'Recibido Completo', 'Pendiente Visita Medico', 'Pendiente Resultado Lab', 'Enviado a LG', 'Pendiente Respuesta LG', 'Completado', 'Rechazado', 'Cancelado'];
        
        $pageTitle = 'Listado de Servicios';
        $currentRoute = 'servicios_index';
        $contentView = __DIR__ . '/../views/servicios/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function exportToExcel() {
        check_permission();
        // --- INICIO DE LÓGICA DE FILTROS AVANZADOS (PARA EXPORTACIÓN) ---
        $filters = [];
        $estadoSeleccionado = $_GET['filtro_estado'] ?? 'en_proceso';

        if ($estadoSeleccionado === 'en_proceso') {
            $filters['estado_not_in'] = ['Completado', 'Rechazado', 'Cancelado'];
        } elseif (!empty($estadoSeleccionado)) {
            $filters['estado'] = $estadoSeleccionado;
        }

        if (!empty($_GET['filtro_socio_id'])) $filters['socio_id'] = filter_input(INPUT_GET, 'filtro_socio_id', FILTER_VALIDATE_INT);
        if (!empty($_GET['filtro_tipo_id'])) $filters['tipo_servicio_id'] = filter_input(INPUT_GET, 'filtro_tipo_id', FILTER_VALIDATE_INT);
        // NUEVOS FILTROS
        if (!empty($_GET['filtro_medico_id'])) $filters['medico_id'] = filter_input(INPUT_GET, 'filtro_medico_id', FILTER_VALIDATE_INT);
        if (!empty($_GET['filtro_fecha_inicio'])) $filters['fecha_inicio'] = $_GET['filtro_fecha_inicio'];
        if (!empty($_GET['filtro_fecha_fin'])) $filters['fecha_fin'] = $_GET['filtro_fecha_fin'];
        // --- FIN DE LÓGICA DE FILTROS AVANZADOS ---
        
        $servicios = Servicio::getAll($filters, -1);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'ID Servicio')->setCellValue('B1', 'Tipo Servicio')->setCellValue('C1', 'Código Servicio')->setCellValue('D1', 'Socio')->setCellValue('E1', 'Cód. Ganadero')->setCellValue('F1', 'Ejemplar')->setCellValue('G1', 'Estado')->setCellValue('H1', 'Fecha Solicitud')->setCellValue('I1', 'Fecha Finalización')->setCellValue('J1', 'Referencia Pago')->setCellValue('K1', 'Última Modificación por');
        
        $row = 2;
        foreach ($servicios as $servicio) {
            $sheet->setCellValue('A' . $row, $servicio['id_servicio'])
                  ->setCellValue('B' . $row, $servicio['tipo_servicio_nombre'])
                  ->setCellValue('C' . $row, $servicio['codigo_servicio'])
                  ->setCellValue('D' . $row, $servicio['socio_nombre'] . ' ' . $servicio['socio_apPaterno'])
                  ->setCellValue('E' . $row, $servicio['socio_codigo_ganadero'])
                  ->setCellValue('F' . $row, $servicio['ejemplar_nombre'])
                  ->setCellValue('G' . $row, $servicio['estado'])
                  ->setCellValue('H' . $row, !empty($servicio['fechaSolicitud']) ? date('d/m/Y', strtotime($servicio['fechaSolicitud'])) : '-')
                  ->setCellValue('I' . $row, !empty($servicio['fechaFinalizacion']) ? date('d/m/Y', strtotime($servicio['fechaFinalizacion'])) : '-')
                  ->setCellValue('J' . $row, $servicio['referencia_pago'])
                  ->setCellValue('K' . $row, $servicio['modificador_username']);
            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Servicios.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function create() {
        check_permission();
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);
        
        $tiposServicioList = TipoServicio::getActiveForSelect();
        $sociosList = Socio::getActiveSociosForSelect();
        $ejemplares = []; // <-- LÍNEA MODIFICADA: Ya no cargamos todos los ejemplares
        $medicosList = Medico::getActiveMedicosForSelect();
        
        $tiposServicioDataJS = [];
        if (!empty($tiposServicioList)) {
            $allTiposData = TipoServicio::getAll();
            foreach($allTiposData as $tipo) {
                if (isset($tiposServicioList[$tipo['id_tipo_servicio']])) {
                    $tiposServicioDataJS[$tipo['id_tipo_servicio']] = [ 'req_medico' => !empty($tipo['requiere_medico']) ];
                }
            }
        }
        
        if (empty($sociosList)) $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . "No hay socios activos.";
        if (empty($tiposServicioList)) $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . "No hay tipos de servicio activos.";
        
        $pageTitle = 'Registrar Nuevo Servicio';
        $currentRoute = 'servicios/create';
        $contentView = __DIR__ . '/../views/servicios/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function store() {
        check_permission();
        $userId = $_SESSION['user']['id_usuario'];
        $_SESSION['form_data'] = $_POST;
        
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
            if (empty($ejemplar_id)) $errors[] = "Debe seleccionar un ejemplar.";
            if (!Socio::getById($socio_id)) $errors[] = "El socio no es válido.";
            
            $tipoServicio = TipoServicio::getById($tipo_servicio_id);
            if (!$tipoServicio) { $errors[] = "El tipo de servicio no es válido."; }
            else { if (!empty($tipoServicio['requiere_medico']) && empty($medico_id)) { $errors[] = "Este servicio requiere un médico."; } }
            
            $ejemplar = Ejemplar::getById($ejemplar_id);
            if (!$ejemplar) { $errors[] = "El ejemplar no es válido."; }
            elseif ($ejemplar['socio_id'] != $socio_id) { $errors[] = "El ejemplar no pertenece al socio."; }
            
            if (!empty($medico_id) && !Medico::getById($medico_id)) $errors[] = "El médico no es válido.";
            if (!isset($_FILES['solicitud_file']) || $_FILES['solicitud_file']['error'] === UPLOAD_ERR_NO_FILE) $errors[] = "Debe adjuntar la Solicitud de Servicio.";
            if (!isset($_FILES['pago_file']) || $_FILES['pago_file']['error'] === UPLOAD_ERR_NO_FILE) $errors[] = "Debe adjuntar el Comprobante de Pago.";
            
            if (!empty($errors)) { $_SESSION['error'] = implode("<br>", $errors); header("Location: index.php?route=servicios/create"); exit; }

            $data = [ 'socio_id' => $socio_id, 'ejemplar_id' => $ejemplar_id, 'tipo_servicio_id' => $tipo_servicio_id, 'medico_id' => $medico_id ?: null, 'estado' => 'Recibido Completo', 'fechaSolicitud' => $fechaSolicitud, 'descripcion' => $descripcion ?: null, 'referencia_pago' => $referencia_pago ?: null, 'id_usuario_registro' => $userId, 'id_usuario_ultima_mod' => $userId, 'fechaRecepcionDocs' => date('Y-m-d'), 'fechaPago' => date('Y-m-d'), 'fechaAsignacionMedico' => ($medico_id ? date('Y-m-d') : null), ];
            
            $servicioId = Servicio::store($data);
            
            if ($servicioId) {
                $desc_audit = "Se creó el servicio para el ejemplar ID: {$ejemplar_id}";
                Auditoria::registrar('CREACIÓN DE SERVICIO', $servicioId, 'Servicio', $desc_audit);

                $_SESSION['message'] = "Servicio registrado con ID: " . $servicioId . ".";
                unset($_SESSION['form_data']);
                
                $this->handleServicioDocumentUpload('solicitud_file', $servicioId, 'SOLICITUD_SERVICIO', $userId);
                $this->handleServicioDocumentUpload('pago_file', $servicioId, 'COMPROBANTE_PAGO', $userId);
                
                header("Location: index.php?route=servicios_index");
                exit;
            } else {
                $_SESSION['error'] = "Error al registrar el servicio: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=servicios/create");
                exit;
            }
        }
    }
    
    public function edit($id = null) {
        check_permission();
        $servicioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$servicioId) { $_SESSION['error'] = "ID inválido."; header("Location: index.php?route=servicios_index"); exit; }
        
        $servicio = Servicio::getById($servicioId);
        if (!$servicio) { $_SESSION['error'] = "Servicio no encontrado."; header("Location: index.php?route=servicios_index"); exit; }
        
        $formData = $_SESSION['form_data'] ?? $servicio;
        unset($_SESSION['form_data']);
        
        $tiposServicioList = TipoServicio::getActiveForSelect();
        $sociosList = Socio::getActiveSociosForSelect();
        $ejemplares = []; // No es necesario cargarlos todos aquí
        $medicosList = Medico::getActiveMedicosForSelect();
        
        $posiblesEstados = Servicio::getSiguientesEstadosPosibles($servicio['estado'], $servicio['flujo_trabajo']);
        $documentosServicio = Documento::getByEntityId('servicio', $servicioId);
        $historialServicio = ServicioHistorial::getByServicioId($servicioId);
        
        $pageTitle = 'Editar/Ver Servicio #' . $servicio['id_servicio'];
        $currentRoute = 'servicios/edit';
        $contentView = __DIR__ . '/../views/servicios/edit.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

     public function update() {
        check_permission();
        $userId = $_SESSION['user']['id_usuario'];
        $id = filter_input(INPUT_POST, 'id_servicio', FILTER_VALIDATE_INT);
        if (!$id) { $_SESSION['error'] = "ID de servicio inválido."; header("Location: index.php?route=servicios_index"); exit; }
        
        // Obtenemos el estado del servicio ANTES de cualquier cambio.
        $servicioAntes = Servicio::getById($id);
        if (!$servicioAntes) {
            $_SESSION['error'] = "Servicio no encontrado para actualizar.";
            header("Location: index.php?route=servicios_index");
            exit;
        }

        $_SESSION['form_data'] = $_POST;
        if (isset($_POST)) {
            $data = [ 
                // --- LÍNEA MODIFICADA ---
                // Si no se recibe un estado (porque está deshabilitado), usa el que ya existía.
                'estado' => trim($_POST['estado'] ?? $servicioAntes['estado']),
                // --- FIN DE LA MODIFICACIÓN ---
                'medico_id' => filter_input(INPUT_POST, 'medico_id', FILTER_VALIDATE_INT) ?: null, 
                'descripcion' => trim($_POST['descripcion'] ?? '') ?: null, 
                'motivo_rechazo' => ($_POST['estado'] === 'Rechazado') ? trim($_POST['motivo_rechazo'] ?? '') : null, 
                'referencia_pago' => trim($_POST['referencia_pago'] ?? '') ?: null, 
                'id_usuario_ultima_mod' => $userId 
            ];

            if (Servicio::update($id, $data)) {
                $descripcion_auditoria = "Se actualizaron los datos del servicio.";
                if ($servicioAntes && $servicioAntes['estado'] !== $data['estado']) {
                    $descripcion_auditoria = "Cambio de estado de '{$servicioAntes['estado']}' a '{$data['estado']}'.";
                }
                Auditoria::registrar('MODIFICACIÓN DE SERVICIO', $id, 'Servicio', $descripcion_auditoria);
                
                $_SESSION['message'] = "Servicio #" . $id . " actualizado.";
                unset($_SESSION['form_data']);

                // Manejo de subida de archivos
                $this->handleServicioDocumentUpload('solicitud_file', $id, 'SOLICITUD_SERVICIO', $userId);
                $this->handleServicioDocumentUpload('pago_file', $id, 'COMPROBANTE_PAGO', $userId);
                
                header("Location: index.php?route=servicios/edit&id=" . $id);
                exit;
            } else {
                $_SESSION['error'] = "Error al actualizar el servicio: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=servicios/edit&id=" . $id);
                exit;
            }
        }
    }
    
    public function cancel($id = null) {
         check_permission();
         $servicioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
         if ($servicioId) {
             if (Servicio::cancel($servicioId, $_SESSION['user']['id_usuario'])) {
                Auditoria::registrar('CANCELACIÓN DE SERVICIO', $servicioId, 'Servicio', 'El servicio fue cancelado por el usuario desde el listado.');
                $_SESSION['message'] = "Servicio #" . $servicioId . " cancelado exitosamente.";
             } else {
                  $_SESSION['error'] = "Error al cancelar el servicio: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                  unset($_SESSION['error_details']);
             }
         } else { 
             $_SESSION['error'] = "ID de servicio inválido.";
         }
         header("Location: index.php?route=servicios_index");
         exit;
    }

    public function updateStatus() {
        check_permission();
        header('Content-Type: application/json');
        
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $estado = trim($_POST['estado'] ?? '');
        $motivoRechazo = ($estado === 'Rechazado') ? trim($_POST['motivo'] ?? '') : null;
        $userId = $_SESSION['user']['id_usuario'];

        if (!$id || empty($estado)) {
            echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
            exit;
        }
        
        $servicioActual = Servicio::getById($id);
        if (!$servicioActual) {
            echo json_encode(['status' => 'error', 'message' => 'Servicio no encontrado.']);
            exit;
        }

        $siguientesEstadosValidos = Servicio::getSiguientesEstadosPosibles($servicioActual['estado'], $servicioActual['flujo_trabajo']);
        if (!in_array($estado, $siguientesEstadosValidos)) {
            echo json_encode(['status' => 'error', 'message' => "Transición de estado no válida de '{$servicioActual['estado']}' a '{$estado}'."]);
            exit;
        }

        if ($estado === 'Rechazado' && empty($motivoRechazo)) {
            echo json_encode(['status' => 'error', 'message' => 'El motivo de rechazo es obligatorio.']);
            exit;
        }

        if (Servicio::updateStatus($id, $estado, $motivoRechazo, $userId)) {
            $descripcion = "Cambio de estado de '{$servicioActual['estado']}' a '{$estado}'.";
            if ($motivoRechazo) { $descripcion .= " Motivo: " . $motivoRechazo; }
            Auditoria::registrar('CAMBIO DE ESTADO ', $id, 'Servicio', $descripcion);
            
            echo json_encode(['status' => 'success', 'message' => 'Estado actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estado. ' . ($_SESSION['error_details'] ?? '')]);
            unset($_SESSION['error_details']);
        }
        exit;
    }

    public function getValidNextStates() {
        header('Content-Type: application/json');
        $servicioId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$servicioId) {
            echo json_encode([]);
            exit;
        }

        $servicio = Servicio::getById($servicioId);
        if ($servicio) {
            $posiblesEstados = Servicio::getSiguientesEstadosPosibles($servicio['estado'], $servicio['flujo_trabajo']);
            echo json_encode($posiblesEstados);
        } else {
            echo json_encode([]);
        }
        exit;
    }
}