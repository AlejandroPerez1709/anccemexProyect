<?php
// app/controllers/MedicosController.php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Incluimos el Validador. Las reglas se cargarán dentro de cada método.
require_once __DIR__ . '/../../core/Validator.php';

require_once __DIR__ . '/../models/Medico.php';
require_once __DIR__ . '/../models/Auditoria.php';
require_once __DIR__ . '/../../config/config.php';

class MedicosController {

    public function index() {
        check_permission();
        $searchTerm = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $records_per_page = 15;
        $offset = ($page - 1) * $records_per_page;

        $total_records = Medico::countAll($searchTerm);
        $total_pages = ceil($total_records / $records_per_page);

        $medicos = Medico::getAll($searchTerm, $records_per_page, $offset);
        
        $pageTitle = 'Listado de Médicos';
        $currentRoute = 'medicos_index';
        $contentView = __DIR__ . '/../views/medicos/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function exportToExcel() {
        check_permission();
        $searchTerm = $_GET['search'] ?? '';
        $medicos = Medico::getAll($searchTerm, -1); 

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'ID')->setCellValue('B1', 'Nombre')->setCellValue('C1', 'Apellido Paterno')->setCellValue('D1', 'Apellido Materno')->setCellValue('E1', 'Especialidad')->setCellValue('F1', 'Teléfono')->setCellValue('G1', 'Email')->setCellValue('H1', 'Cédula Profesional')->setCellValue('I1', 'Certificación ANCCE')->setCellValue('J1', 'Estado');
        
        $row = 2;
        foreach ($medicos as $medico) {
            $sheet->setCellValue('A' . $row, $medico['id_medico'])
                  ->setCellValue('B' . $row, $medico['nombre'])
                  ->setCellValue('C' . $row, $medico['apellido_paterno'])
                  ->setCellValue('D' . $row, $medico['apellido_materno'])
                  ->setCellValue('E' . $row, $medico['especialidad'])
                  ->setCellValue('F' . $row, $medico['telefono'])
                  ->setCellValue('G' . $row, $medico['email'])
                  ->setCellValue('H' . $row, $medico['numero_cedula_profesional'])
                  ->setCellValue('I' . $row, $medico['numero_certificacion_ancce'])
                  ->setCellValue('J' . $row, ucfirst($medico['estado']));
            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Medicos.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function create() {
        check_permission();
        $errors = $_SESSION['errors'] ?? [];
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['errors'], $_SESSION['form_data']);

        $pageTitle = 'Registrar Nuevo Médico';
        $currentRoute = 'medicos/create';
        $contentView = __DIR__ . '/../views/medicos/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function store() {
        check_permission();
        $rules = require __DIR__ . '/../../config/validation_rules.php';

        $errors = Validator::validate($_POST, $rules['crear_medico']);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header("Location: index.php?route=medicos/create");
            exit;
        }

        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
            'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
            'especialidad' => trim($_POST['especialidad'] ?? '') ?: null,
            'telefono' => trim($_POST['telefono'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'numero_cedula_profesional' => trim($_POST['numero_cedula_profesional'] ?? ''),
            'entidad_residencia' => trim($_POST['entidad_residencia'] ?? ''),
            'numero_certificacion_ancce' => trim($_POST['numero_certificacion_ancce'] ?? '') ?: null,
            'estado' => trim($_POST['estado'] ?? 'activo'),
            'id_usuario' => $_SESSION['user']['id_usuario']
        ];
        
        $newId = Medico::store($data);
        if($newId) {
            $descripcion = "Se creó el médico: " . $data['nombre'] . " " . $data['apellido_paterno'];
            Auditoria::registrar('CREACIÓN DE MÉDICO', $newId, 'Medico', $descripcion);

            $_SESSION['message'] = "Médico registrado exitosamente.";
            header("Location: index.php?route=medicos_index");
            exit;
        } else {
            $_SESSION['form_data'] = $_POST;
            $_SESSION['error'] = "Error al registrar el médico: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
            unset($_SESSION['error_details']);
            header("Location: index.php?route=medicos/create");
            exit;
        }
    }

    public function edit($id = null) {
        check_permission();
        $medicoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($medicoId) {
            $medico = Medico::getById($medicoId);
            if($medico) {
                $errors = $_SESSION['errors'] ?? [];
                $formData = $_SESSION['form_data'] ?? $medico;
                unset($_SESSION['errors'], $_SESSION['form_data']);
                
                $pageTitle = 'Editar Médico';
                $currentRoute = 'medicos/edit';
                $contentView = __DIR__ . '/../views/medicos/edit.php'; 
                require_once __DIR__ . '/../views/layouts/master.php'; 
                return;
            }
        }
        $_SESSION['error'] = "Médico no encontrado.";
        header("Location: index.php?route=medicos_index"); 
        exit;
    }

    public function update() {
        check_permission();
        $rules = require __DIR__ . '/../../config/validation_rules.php';

        $id = filter_input(INPUT_POST, 'id_medico', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de médico inválido.";
            header("Location: index.php?route=medicos_index");
            exit;
        }

        $updateRules = $rules['actualizar_medico'];
        $updateRules['email'] .= "|unique:medicos,email," . $id;
        $updateRules['numero_cedula_profesional'] .= "|unique:medicos,numero_cedula_profesional," . $id;

        $errors = Validator::validate($_POST, $updateRules);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header("Location: index.php?route=medicos/edit&id=" . $id);
            exit;
        }

        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
            'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
            'especialidad' => trim($_POST['especialidad'] ?? '') ?: null,
            'telefono' => trim($_POST['telefono'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'numero_cedula_profesional' => trim($_POST['numero_cedula_profesional'] ?? ''),
            'entidad_residencia' => trim($_POST['entidad_residencia'] ?? ''),
            'numero_certificacion_ancce' => trim($_POST['numero_certificacion_ancce'] ?? '') ?: null,
            'estado' => trim($_POST['estado'] ?? 'activo'),
            'id_usuario' => $_SESSION['user']['id_usuario']
        ];
        
        if(Medico::update($id, $data)) {
            $descripcion = "Se modificaron los datos del médico: " . $data['nombre'] . " " . $data['apellido_paterno'];
            Auditoria::registrar('MODIFICACIÓN DE MÉDICO', $id, 'Medico', $descripcion);

            $_SESSION['message'] = "Médico actualizado exitosamente.";
            header("Location: index.php?route=medicos_index");
            exit;
        } else {
            $_SESSION['form_data'] = $_POST;
            $_SESSION['error'] = "Error al actualizar el médico: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
            unset($_SESSION['error_details']);
            header("Location: index.php?route=medicos/edit&id=" . $id);
            exit;
        }
    }

    public function delete($id = null) {
        check_permission();
        $medicoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $razon = $_POST['razon'] ?? '';
        if (empty($razon)) {
            $_SESSION['error'] = "La razón de desactivación es obligatoria.";
            header("Location: index.php?route=medicos_index");
            exit;
        }

        if($medicoId) {
            $medico = Medico::getById($medicoId);
            if(Medico::delete($medicoId, $razon)) {
                $nombreMedico = $medico ? $medico['nombre'] . ' ' . $medico['apellido_paterno'] : 'ID ' . $medicoId;
                $descripcion = "Se desactivó al médico: " . $nombreMedico . ". Razón: " . $razon;
                Auditoria::registrar('DESACTIVACIÓN DE MÉDICO', $medicoId, 'Medico', $descripcion);

                $_SESSION['message'] = "Médico desactivado exitosamente.";
            } else { 
                $_SESSION['error'] = "Error al desactivar el médico. " . ($_SESSION['error_details'] ?? 'Puede que tenga servicios asociados.'); 
                unset($_SESSION['error_details']);
            }
        } else { 
            $_SESSION['error'] = "ID de médico no especificado.";
        }
        header("Location: index.php?route=medicos_index"); 
        exit;
    }
}