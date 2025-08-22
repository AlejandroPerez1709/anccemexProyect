<?php
// app/controllers/EmpleadosController.php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../models/Auditoria.php'; 
require_once __DIR__ . '/../../config/config.php';
class EmpleadosController {

    public function index() {
        check_permission();
        $searchTerm = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $records_per_page = 15;
        $offset = ($page - 1) * $records_per_page;
        $total_records = Empleado::countAll($searchTerm);
        $total_pages = ceil($total_records / $records_per_page);

        $empleados = Empleado::getAll($searchTerm, $records_per_page, $offset);

        $pageTitle = 'Listado de Empleados';
        $currentRoute = 'empleados_index';
        $contentView = __DIR__ . '/../views/empleados/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function exportToExcel() {
        check_permission();
        $searchTerm = $_GET['search'] ?? '';
        $empleados = Empleado::getAll($searchTerm, -1);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID')->setCellValue('B1', 'Nombre')->setCellValue('C1', 'Apellido Paterno')->setCellValue('D1', 'Apellido Materno')->setCellValue('E1', 'Email')->setCellValue('F1', 'Dirección')->setCellValue('G1', 'Teléfono')->setCellValue('H1', 'Puesto')->setCellValue('I1', 'Fecha Ingreso')->setCellValue('J1', 'Estado');
        $row = 2;
        foreach ($empleados as $empleado) {
            $sheet->setCellValue('A' . $row, $empleado['id_empleado'])
                  ->setCellValue('B' . $row, $empleado['nombre'])
                  ->setCellValue('C' . $row, $empleado['apellido_paterno'])
                  ->setCellValue('D' . $row, $empleado['apellido_materno'])
                   ->setCellValue('E' . $row, $empleado['email'])
                  ->setCellValue('F' . $row, $empleado['direccion'])
                  ->setCellValue('G' . $row, $empleado['telefono'])
                  ->setCellValue('H' . $row, $empleado['puesto'])
                  ->setCellValue('I' . $row, !empty($empleado['fecha_ingreso']) ? date('d/m/Y', strtotime($empleado['fecha_ingreso'])) : '-')
                   ->setCellValue('J' . $row, ucfirst($empleado['estado']));
            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Empleados.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function create() {
        check_permission();
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']); 

        $pageTitle = 'Registrar Nuevo Empleado';
        $currentRoute = 'empleados/create';
        $contentView = __DIR__ . '/../views/empleados/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function store() {
        check_permission();
        $_SESSION['form_data'] = $_POST;
        if(isset($_POST)) {
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
                'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                 'direccion' => trim($_POST['direccion'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'puesto' => trim($_POST['puesto'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'activo'), 
                'fecha_ingreso' => trim($_POST['fecha_ingreso'] ?? '')
             ];
            
            // --- INICIO DE MODIFICACIÓN: Usar el ID devuelto por el método store ---
            $newId = Empleado::store($data);
            if($newId) {
                $descripcion = "Se creó el empleado: " . $data['nombre'] . " " . $data['apellido_paterno'];
                Auditoria::registrar('CREACIÓN DE EMPLEADO', $newId, 'Empleado', $descripcion);
            // --- FIN DE MODIFICACIÓN ---

                $_SESSION['message'] = "Empleado creado exitosamente.";
                unset($_SESSION['form_data']);
                header("Location: index.php?route=empleados_index");
                exit;
            } else {
                $_SESSION['error'] = "Error al crear el empleado: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=empleados/create"); 
                exit;
            }
        }
    }

    public function edit($id = null) {
        check_permission();
        $empleadoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($empleadoId) {
            $empleado = Empleado::getById($empleadoId);
            if($empleado) {
                $formData = $_SESSION['form_data'] ?? $empleado; 
                unset($_SESSION['form_data']); 
                
                $pageTitle = 'Editar Empleado';
                $currentRoute = 'empleados/edit';
                $contentView = __DIR__ . '/../views/empleados/edit.php'; 
                require_once __DIR__ . '/../views/layouts/master.php'; 
                return;
            }
        }
        $_SESSION['error'] = "Empleado no encontrado.";
        header("Location: index.php?route=empleados_index"); 
        exit;
    }

    public function update() {
        check_permission();
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de empleado inválido.";
            header("Location: index.php?route=empleados_index"); 
            exit;
        }
        
        $_SESSION['form_data'] = $_POST;
        if(isset($_POST)) {
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''), 
                'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''), 
                'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
                'email' => trim($_POST['email'] ?? ''), 
                 'direccion' => trim($_POST['direccion'] ?? ''), 
                'telefono' => trim($_POST['telefono'] ?? ''),
                'puesto' => trim($_POST['puesto'] ?? ''), 
                'estado' => trim($_POST['estado'] ?? 'activo'),
                'fecha_ingreso' => trim($_POST['fecha_ingreso'] ?? '')
            ];
            if(Empleado::update($id, $data)) {
                $descripcion = "Se modificaron los datos del empleado: " . $data['nombre'] . " " . $data['apellido_paterno'];
                Auditoria::registrar('MODIFICACIÓN DE EMPLEADO', $id, 'Empleado', $descripcion);

                $_SESSION['message'] = "Empleado actualizado exitosamente.";
                unset($_SESSION['form_data']);
                header("Location: index.php?route=empleados_index"); 
                exit;
            } else {
                $_SESSION['error'] = "Error al actualizar el empleado: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=empleados/edit&id=" . $id); 
                exit;
            }
        }
    }

    public function delete($id = null) {
        check_permission();
        $empleadoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $razon = $_POST['razon'] ?? '';
        if (empty($razon)) {
            $_SESSION['error'] = "La razón de desactivación es obligatoria.";
            header("Location: index.php?route=empleados_index");
            exit;
        }

        if($empleadoId) {
            $empleado = Empleado::getById($empleadoId); // Se obtiene el empleado ANTES de desactivarlo
            if(Empleado::delete($empleadoId, $razon)) {
                $nombreEmpleado = $empleado ? $empleado['nombre'] . ' ' . $empleado['apellido_paterno'] : 'ID ' . $empleadoId;
                $descripcion = "Se desactivó al empleado: " . $nombreEmpleado . ". Razón: " . $razon;
                Auditoria::registrar('DESACTIVACIÓN DE EMPLEADO', $empleadoId, 'Empleado', $descripcion);

                $_SESSION['message'] = "Empleado desactivado.";
            } else { 
                $_SESSION['error'] = "Error al desactivar. " . ($_SESSION['error_details'] ?? 'Puede tener registros asociados.'); 
                unset($_SESSION['error_details']);
            }
        } else { 
            $_SESSION['error'] = "ID inválido.";
        }
        header("Location: index.php?route=empleados_index"); 
        exit;
    }
}