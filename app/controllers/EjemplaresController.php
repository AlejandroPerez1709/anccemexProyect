<?php
// app/controllers/EjemplaresController.php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once __DIR__ . '/../models/Ejemplar.php';
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Documento.php';
require_once __DIR__ . '/../models/Auditoria.php'; // <-- INCLUIMOS EL MODELO DE AUDITORÍA
require_once __DIR__ . '/../../config/config.php';

class EjemplaresController {

    private function handleSingleEjemplarDocUpload($fileInputName, $ejemplarId, $tipoDocumento, $userId) {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 10 * 1024 * 1024;

            $uploadResult = Documento::handleUpload($fileInputName, $allowedTypes, $maxFileSize, 'ejemplares');
            if ($uploadResult['status'] === 'success') {
                $docData = [ 'tipoDocumento' => $tipoDocumento, 'nombreArchivoOriginal' => $uploadResult['data']['originalName'], 'rutaArchivo' => $uploadResult['data']['savedPath'], 'mimeType' => $uploadResult['data']['mimeType'], 'sizeBytes' => $uploadResult['data']['size'], 'socio_id' => null, 'ejemplar_id' => $ejemplarId, 'servicio_id' => null, 'id_usuario' => $userId, 'comentarios' => 'Documento maestro de ejemplar.' ];
                if (!Documento::store($docData)) {
                     $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error DB doc: " . htmlspecialchars($uploadResult['data']['originalName']) . ". ";
                }
            } else {
                 $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error al subir " . htmlspecialchars($_FILES[$fileInputName]['name']) . ": " . $uploadResult['message'] . ". ";
            }
        }
    }

    private function handleMultipleEjemplarPhotos($fileInputName, $ejemplarId, $userId) {
        if (isset($_FILES[$fileInputName]) && is_array($_FILES[$fileInputName]['name']) && !empty($_FILES[$fileInputName]['name'][0])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 5 * 1024 * 1024;
            $fileCount = count($_FILES[$fileInputName]['name']);
            $subfolder = 'ejemplares' . DIRECTORY_SEPARATOR . $ejemplarId . DIRECTORY_SEPARATOR . 'fotos';

            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES[$fileInputName]['error'][$i] === UPLOAD_ERR_OK) {
                    $singleFileArray = [ 'name' => $_FILES[$fileInputName]['name'][$i], 'type' => $_FILES[$fileInputName]['type'][$i], 'tmp_name' => $_FILES[$fileInputName]['tmp_name'][$i], 'error' => $_FILES[$fileInputName]['error'][$i], 'size' => $_FILES[$fileInputName]['size'][$i] ];
                    $uploadResult = Documento::handleUpload('dummy_name', $allowedTypes, $maxFileSize, $subfolder, ['dummy_name' => $singleFileArray]);

                    if ($uploadResult['status'] === 'success') {
                        $docData = [ 'tipoDocumento' => 'FOTO_IDENTIFICACION', 'nombreArchivoOriginal' => $uploadResult['data']['originalName'], 'rutaArchivo' => $uploadResult['data']['savedPath'], 'mimeType' => $uploadResult['data']['mimeType'], 'sizeBytes' => $uploadResult['data']['size'], 'socio_id' => null, 'ejemplar_id' => $ejemplarId, 'servicio_id' => null, 'id_usuario' => $userId, 'comentarios' => 'Foto ID.' ];
                        if (!Documento::store($docData)) {
                             $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error DB foto: " . htmlspecialchars($singleFileArray['name']) . ". ";
                        }
                    } else {
                        $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error al subir " . htmlspecialchars($singleFileArray['name']) . ": " . $uploadResult['message'] . ". ";
                    }
                }
            }
        }
    }

    public function index() {
         check_permission();
         $searchTerm = $_GET['search'] ?? '';
         $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
         $records_per_page = 15;
         $offset = ($page - 1) * $records_per_page;

         $total_records = Ejemplar::countAll($searchTerm);
         $total_pages = ceil($total_records / $records_per_page);

         $ejemplares = Ejemplar::getAll($searchTerm, $records_per_page, $offset);
        foreach ($ejemplares as $key => $ejemplar) {
            $ejemplares[$key]['document_status'] = Documento::getDocumentStatusForEjemplar($ejemplar['id_ejemplar']);
        }

         $pageTitle = 'Listado de Ejemplares';
         $currentRoute = 'ejemplares_index'; 
         $contentView = __DIR__ . '/../views/ejemplares/index.php'; 
         require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function exportToExcel() {
        check_permission();

        $searchTerm = $_GET['search'] ?? '';
        $ejemplares = Ejemplar::getAll($searchTerm, -1); // -1 para obtener todos los registros

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->setCellValue('A1', 'ID')->setCellValue('B1', 'Nombre')->setCellValue('C1', 'Código Ejemplar')->setCellValue('D1', 'Socio Propietario')->setCellValue('E1', 'Cód. Ganadero')->setCellValue('F1', 'Sexo')->setCellValue('G1', 'Fecha Nacimiento')->setCellValue('H1', 'Raza')->setCellValue('I1', 'Capa')->setCellValue('J1', 'N° Microchip')->setCellValue('K1', 'Estado');
        $row = 2;
        foreach ($ejemplares as $ejemplar) {
            $sheet->setCellValue('A' . $row, $ejemplar['id_ejemplar'])
                  ->setCellValue('B' . $row, $ejemplar['nombre'])
                  ->setCellValue('C' . $row, $ejemplar['codigo_ejemplar'])
                  ->setCellValue('D' . $row, $ejemplar['nombre_socio'])
                   ->setCellValue('E' . $row, $ejemplar['socio_codigo_ganadero'])
                  ->setCellValue('F' . $row, $ejemplar['sexo'])
                  ->setCellValue('G' . $row, !empty($ejemplar['fechaNacimiento']) ? date('d/m/Y', strtotime($ejemplar['fechaNacimiento'])) : '-')
                  ->setCellValue('H' . $row, $ejemplar['raza'])
                  ->setCellValue('I' . $row, $ejemplar['capa'])
                   ->setCellValue('J' . $row, $ejemplar['numero_microchip'])
                  ->setCellValue('K' . $row, ucfirst($ejemplar['estado']));
            $row++;
        }

        // Cabeceras para descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Ejemplares.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function create() {
        check_permission();
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);

        $sociosList = Socio::getActiveSociosForSelect();
        if (empty($sociosList)) { 
            $_SESSION['warning'] = "No hay socios activos. Por favor, registre uno primero.";
        }
        
        $pageTitle = 'Registrar Nuevo Ejemplar';
        $currentRoute = 'ejemplares/create';
        $contentView = __DIR__ . '/../views/ejemplares/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function store() {
        check_permission();
        $userId = $_SESSION['user']['id_usuario'];
        $_SESSION['form_data'] = $_POST;

        if(isset($_POST)) {
            $data = [ 'nombre' => trim($_POST['nombre'] ?? ''), 'raza' => trim($_POST['raza'] ?? '') ?: null, 'fechaNacimiento' => trim($_POST['fechaNacimiento'] ?? '') ?: null, 'socio_id' => filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT), 'sexo' => trim($_POST['sexo'] ?? ''), 'codigo_ejemplar' => trim($_POST['codigo_ejemplar'] ?? '') ?: null, 'capa' => trim($_POST['capa'] ?? '') ?: null, 'numero_microchip' => trim($_POST['numero_microchip'] ?? '') ?: null, 'numero_certificado' => trim($_POST['numero_certificado'] ?? '') ?: null, 'estado' => trim($_POST['estado'] ?? 'activo'), 'id_usuario' => $userId ];
            $ejemplarId = Ejemplar::store($data);
            if($ejemplarId) {
                // --- REGISTRAMOS LA ACCIÓN EN LA AUDITORÍA ---
                $descripcion = "Se creó el ejemplar: " . $data['nombre'] . " (Cód: " . $data['codigo_ejemplar'] . ")";
                Auditoria::registrar('CREACIÓN DE EJEMPLAR', $ejemplarId, 'Ejemplar', $descripcion);

                $_SESSION['message'] = "Ejemplar registrado con ID: " . $ejemplarId . "."; 
                unset($_SESSION['form_data']);

                $this->handleSingleEjemplarDocUpload('pasaporte_file', $ejemplarId, 'PASAPORTE_DIE', $userId);
                $this->handleSingleEjemplarDocUpload('adn_file', $ejemplarId, 'RESULTADO_ADN', $userId);
                $this->handleSingleEjemplarDocUpload('cert_lg_file', $ejemplarId, 'CERTIFICADO_INSCRIPCION_LG', $userId);
                $this->handleMultipleEjemplarPhotos('fotos_file', $ejemplarId, $userId);
                header("Location: index.php?route=ejemplares_index"); 
                exit;
            } else { 
                $_SESSION['error'] = "Error al registrar ejemplar: " . ($_SESSION['error_details'] ?? 'Error desconocido.'); 
                unset($_SESSION['error_details']);
                header("Location: index.php?route=ejemplares/create");
                exit; 
            }
        }
    }

    public function edit($id = null) {
        check_permission();
        $ejemplarId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($ejemplarId) { 
            $ejemplar = Ejemplar::getById($ejemplarId);
            if ($ejemplar) { 
                $formData = $_SESSION['form_data'] ?? $ejemplar;
                unset($_SESSION['form_data']);

                $sociosList = Socio::getActiveSociosForSelect();
                $documentosEjemplar = Documento::getByEntityId('ejemplar', $ejemplarId, true); 
                $pageTitle = 'Editar Ejemplar'; 
                $currentRoute = 'ejemplares/edit';
                $contentView = __DIR__ . '/../views/ejemplares/edit.php';
                require_once __DIR__ . '/../views/layouts/master.php'; 
                return;
            }
        }
        $_SESSION['error'] = "Ejemplar no encontrado o ID inválido.";
        header("Location: index.php?route=ejemplares_index"); 
        exit;
    }

    public function update() {
        check_permission();
        $userId = $_SESSION['user']['id_usuario'];
        $id = filter_input(INPUT_POST, 'id_ejemplar', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de ejemplar inválido.";
            header("Location: index.php?route=ejemplares_index"); 
            exit;
        }
        
        $_SESSION['form_data'] = $_POST;
        if(isset($_POST)) {
            $data = [ 'nombre' => trim($_POST['nombre'] ?? ''), 'raza' => trim($_POST['raza'] ?? '') ?: null, 'fechaNacimiento' => trim($_POST['fechaNacimiento'] ?? '') ?: null, 'socio_id' => filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT), 'sexo' => trim($_POST['sexo'] ?? ''), 'codigo_ejemplar' => trim($_POST['codigo_ejemplar'] ?? '') ?: null, 'capa' => trim($_POST['capa'] ?? '') ?: null, 'numero_microchip' => trim($_POST['numero_microchip'] ?? '') ?: null, 'numero_certificado' => trim($_POST['numero_certificado'] ?? '') ?: null, 'estado' => trim($_POST['estado'] ?? ''), 'id_usuario' => $userId ];
            if(Ejemplar::update($id, $data)) {
                // --- REGISTRAMOS LA ACCIÓN EN LA AUDITORÍA ---
                $descripcion = "Se modificaron los datos del ejemplar: " . $data['nombre'];
                Auditoria::registrar('MODIFICACIÓN DE EJEMPLAR', $id, 'Ejemplar', $descripcion);

                $_SESSION['message'] = "Ejemplar actualizado.";
                unset($_SESSION['form_data']);

                $this->handleSingleEjemplarDocUpload('pasaporte_file', $id, 'PASAPORTE_DIE', $userId);
                $this->handleSingleEjemplarDocUpload('adn_file', $id, 'RESULTADO_ADN', $userId);
                $this->handleSingleEjemplarDocUpload('cert_lg_file', $id, 'CERTIFICADO_INSCRIPCION_LG', $userId);
                $this->handleMultipleEjemplarPhotos('fotos_file', $id, $userId);
            } else { 
                $_SESSION['error'] = "Error al actualizar. " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=ejemplares/edit&id=" . $id);
                exit;
            }
        }
        
        header("Location: index.php?route=ejemplares_index");
        exit;
    }

    public function delete($id = null) {
         check_permission();
         $ejemplarId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
         $razon = $_POST['razon'] ?? '';
         if (empty($razon)) {
            $_SESSION['error'] = "La razón de desactivación es obligatoria.";
            header("Location: index.php?route=ejemplares_index");
            exit;
         }

         if ($ejemplarId) { 
            $ejemplar = Ejemplar::getById($ejemplarId); // Obtenemos datos ANTES de la acción
             if (Ejemplar::delete($ejemplarId, $razon)) {
                // --- REGISTRAMOS LA ACCIÓN EN LA AUDITORÍA ---
                $nombreEjemplar = $ejemplar ? $ejemplar['nombre'] : 'ID ' . $ejemplarId;
                $descripcion = "Se desactivó al ejemplar: " . $nombreEjemplar . ". Razón: " . $razon;
                Auditoria::registrar('DESACTIVACIÓN DE EJEMPLAR', $ejemplarId, 'Ejemplar', $descripcion);

                 $_SESSION['message'] = "Ejemplar desactivado.";
             } else { 
                 $_SESSION['error'] = "Error al desactivar. " . ($_SESSION['error_details'] ?? 'Puede que tenga registros asociados.'); 
                 unset($_SESSION['error_details']);
             } 
         } else { 
             $_SESSION['error'] = "ID inválido.";
         } 
         header("Location: index.php?route=ejemplares_index"); 
         exit;
    }
}