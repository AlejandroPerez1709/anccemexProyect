<?php
// app/controllers/SociosController.php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Documento.php';
require_once __DIR__ . '/../../config/config.php';

class SociosController {

    private function handleSocioDocumentUpload($fileInputName, $socioId, $tipoDocumento, $userId) {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 10 * 1024 * 1024; // 10 MB

        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            $uploadResult = Documento::handleUpload($fileInputName, $allowedTypes, $maxFileSize, 'socios');
            if ($uploadResult['status'] === 'success') {
                $docData = [
                    'tipoDocumento' => $tipoDocumento,
                    'nombreArchivoOriginal' => $uploadResult['data']['originalName'],
                    'rutaArchivo' => $uploadResult['data']['savedPath'],
                    'mimeType' => $uploadResult['data']['mimeType'],
                    'sizeBytes' => $uploadResult['data']['size'],
                    'socio_id' => $socioId,
                    'ejemplar_id' => null,
                    'servicio_id' => null,
                    'id_usuario' => $userId,
                    'comentarios' => 'Documento maestro de socio.'
                ];
                if (!Documento::store($docData)) {
                     error_log("Error BD al guardar doc {$tipoDocumento} para socio ID {$socioId}.");
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
        
        $searchTerm = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $records_per_page = 15;
        $offset = ($page - 1) * $records_per_page;
        
        $total_records = Socio::countAll($searchTerm);
        $total_pages = ceil($total_records / $records_per_page);

        $socios = Socio::getAll($searchTerm, $records_per_page, $offset);

        // INICIO DE LA MODIFICACIÓN: Obtener el estado de los documentos para cada socio
        foreach ($socios as $key => $socio) {
            $socios[$key]['document_status'] = Documento::getDocumentStatusForSocio($socio['id_socio']);
        }
        // FIN DE LA MODIFICACIÓN

        $pageTitle = 'Listado de Socios';
        $currentRoute = 'socios_index';
        $contentView = __DIR__ . '/../views/socios/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function exportToExcel() {
        check_permission();

        $searchTerm = $_GET['search'] ?? '';
        $socios = Socio::getAll($searchTerm, -1); // -1 para indicar sin límite

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID')->setCellValue('B1', 'Nombre')->setCellValue('C1', 'Apellido Paterno')->setCellValue('D1', 'Apellido Materno')->setCellValue('E1', 'Nombre Ganadería')->setCellValue('F1', 'Dirección')->setCellValue('G1', 'Código Ganadero')->setCellValue('H1', 'Teléfono')->setCellValue('I1', 'Email')->setCellValue('J1', 'Fecha Registro')->setCellValue('K1', 'Estado')->setCellValue('L1', 'RFC');
        
        $row = 2;
        foreach ($socios as $socio) {
            $sheet->setCellValue('A' . $row, $socio['id_socio'])->setCellValue('B' . $row, $socio['nombre'])->setCellValue('C' . $row, $socio['apellido_paterno'])->setCellValue('D' . $row, $socio['apellido_materno'])->setCellValue('E' . $row, $socio['nombre_ganaderia'])->setCellValue('F' . $row, $socio['direccion'])->setCellValue('G' . $row, $socio['codigoGanadero'])->setCellValue('H' . $row, $socio['telefono'])->setCellValue('I' . $row, $socio['email'])->setCellValue('J' . $row, $socio['fechaRegistro'])->setCellValue('K' . $row, ucfirst($socio['estado']))->setCellValue('L' . $row, $socio['identificacion_fiscal_titular']);
            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Socios.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function create() {
        check_permission();
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);

        $pageTitle = 'Registrar Nuevo Socio';
        $currentRoute = 'socios/create';
        $contentView = __DIR__ . '/../views/socios/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function store() {
        check_permission();
        $_SESSION['form_data'] = $_POST;
        if(isset($_POST)) {
            $data = [ 'nombre' => trim($_POST['nombre'] ?? ''), 'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''), 'apellido_materno' => trim($_POST['apellido_materno'] ?? ''), 'nombre_ganaderia' => trim($_POST['nombre_ganaderia'] ?? ''), 'direccion' => trim($_POST['direccion'] ?? ''), 'codigoGanadero' => trim($_POST['codigoGanadero'] ?? ''), 'telefono' => trim($_POST['telefono'] ?? ''), 'email' => trim($_POST['email'] ?? ''), 'fechaRegistro' => trim($_POST['fechaRegistro'] ?? date('Y-m-d')), 'estado' => trim($_POST['estado'] ?? 'activo'), 'id_usuario' => $_SESSION['user']['id_usuario'], 'identificacion_fiscal_titular' => trim($_POST['identificacion_fiscal_titular'] ?? '') ];
            $socioId = Socio::store($data);
            if($socioId !== false) {
                $_SESSION['message'] = "Socio registrado exitosamente con ID: " . $socioId . ".";
                unset($_SESSION['form_data']);
                $this->handleSocioDocumentUpload('id_oficial_file', $socioId, 'ID_OFICIAL_TITULAR', $data['id_usuario']);
                $this->handleSocioDocumentUpload('rfc_file', $socioId, 'CONSTANCIA_FISCAL', $data['id_usuario']);
                $this->handleSocioDocumentUpload('domicilio_file', $socioId, 'COMPROBANTE_DOM_GANADERIA', $data['id_usuario']);
                $this->handleSocioDocumentUpload('titulo_propiedad_file', $socioId, 'TITULO_PROPIEDAD_RANCHO', $data['id_usuario']);
                header("Location: index.php?route=socios_index");
                exit;
            } else {
                $_SESSION['error'] = "Error al registrar el socio: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=socios/create");
                exit;
            }
        }
    }
    
    public function edit($id = null) {
        check_permission();
        $socioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($socioId) {
            $socio = Socio::getById($socioId);
            if($socio) {
                $formData = $_SESSION['form_data'] ?? $socio;
                unset($_SESSION['form_data']);
                $documentosSocio = Documento::getByEntityId('socio', $socioId, true);
                $pageTitle = 'Editar Socio'; 
                $currentRoute = 'socios/edit';
                $contentView = __DIR__ . '/../views/socios/edit.php';
                require_once __DIR__ . '/../views/layouts/master.php';
                return;
            }
        }
        $_SESSION['error'] = "Socio no encontrado.";
        header("Location: index.php?route=socios_index"); 
        exit;
    }

    public function update() {
        check_permission();
        $id = filter_input(INPUT_POST, 'id_socio', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de socio inválido.";
            header("Location: index.php?route=socios_index"); 
            exit;
        }

        $_SESSION['form_data'] = $_POST;
        if(isset($_POST)) {
            $data = [ 'nombre' => trim($_POST['nombre'] ?? ''), 'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''), 'apellido_materno' => trim($_POST['apellido_materno'] ?? ''), 'nombre_ganaderia' => trim($_POST['nombre_ganaderia'] ?? ''), 'direccion' => trim($_POST['direccion'] ?? ''), 'codigoGanadero' => trim($_POST['codigoGanadero'] ?? ''), 'telefono' => trim($_POST['telefono'] ?? ''), 'email' => trim($_POST['email'] ?? ''), 'fechaRegistro' => trim($_POST['fechaRegistro'] ?? ''), 'estado' => trim($_POST['estado'] ?? 'activo'), 'id_usuario' => $_SESSION['user']['id_usuario'], 'identificacion_fiscal_titular' => trim($_POST['identificacion_fiscal_titular'] ?? '') ];
            if(Socio::update($id, $data)) {
                $_SESSION['message'] = "Socio actualizado exitosamente.";
                unset($_SESSION['form_data']);
                $this->handleSocioDocumentUpload('id_oficial_file', $id, 'ID_OFICIAL_TITULAR', $data['id_usuario']);
                $this->handleSocioDocumentUpload('rfc_file', $id, 'CONSTANCIA_FISCAL', $data['id_usuario']);
                $this->handleSocioDocumentUpload('domicilio_file', $id, 'COMPROBANTE_DOM_GANADERIA', $data['id_usuario']);
                $this->handleSocioDocumentUpload('titulo_propiedad_file', $id, 'TITULO_PROPIEDAD_RANCHO', $data['id_usuario']);
                header("Location: index.php?route=socios_index");
                exit;
            } else {
                $_SESSION['error'] = "Error al actualizar el socio: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=socios/edit&id=" . $id);
                exit;
            }
        }
    }

    public function delete($id = null) {
        check_permission();
        $socioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $razon = $_POST['razon'] ?? '';
        if (empty($razon)) {
            $_SESSION['error'] = "La razón de desactivación es obligatoria.";
            header("Location: index.php?route=socios_index");
            exit;
        }

        if($socioId) {
            if(Socio::delete($socioId, $razon)) { 
                $_SESSION['message'] = "Socio desactivado exitosamente.";
            } else { 
                $_SESSION['error'] = "Error al desactivar socio. " . ($_SESSION['error_details'] ?? ''); 
                unset($_SESSION['error_details']);
            }
        } else { 
            $_SESSION['error'] = "ID de socio inválido.";
        }
        header("Location: index.php?route=socios_index"); 
        exit;
    }
}