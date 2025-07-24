<?php
// app/controllers/TiposServiciosController.php

// Usamos la librería que instalamos con Composer
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once __DIR__ . '/../models/TipoServicio.php';

class TiposServiciosController {

    public function index() {
        check_permission('superusuario');
        $tiposServicios = TipoServicio::getAll();
        $pageTitle = 'Catálogo de Tipos de Servicio';
        $currentRoute = 'tipos_servicios_index';
        $contentView = __DIR__ . '/../views/tipos_servicios/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function exportToExcel() {
        check_permission('superusuario');

        $tiposServicios = TipoServicio::getAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Nombre');
        $sheet->setCellValue('C1', 'Código de Servicio');
        $sheet->setCellValue('D1', 'Descripción');
        $sheet->setCellValue('E1', 'Requiere Médico');
        $sheet->setCellValue('F1', 'Estado');

        // Llenar datos
        $row = 2;
        foreach ($tiposServicios as $tipo) {
            $sheet->setCellValue('A' . $row, $tipo['id_tipo_servicio']);
            $sheet->setCellValue('B' . $row, $tipo['nombre']);
            $sheet->setCellValue('C' . $row, $tipo['codigo_servicio']);
            $sheet->setCellValue('D' . $row, $tipo['descripcion']);
            $sheet->setCellValue('E' . $row, $tipo['requiere_medico'] ? 'Sí' : 'No');
            $sheet->setCellValue('F' . $row, ucfirst($tipo['estado']));
            $row++;
        }

        // Encabezados para la descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Tipos_de_Servicio.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function create() {
        check_permission('superusuario');
        $pageTitle = 'Registrar Nuevo Tipo de Servicio';
        $currentRoute = 'tipos_servicios/create';
        $contentView = __DIR__ . '/../views/tipos_servicios/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function store() {
        check_permission('superusuario');
        if (isset($_POST)) {
            $data = [ 
                'nombre' => trim($_POST['nombre']), 
                'codigo_servicio' => trim($_POST['codigo_servicio'] ?? ''), 
                'descripcion' => trim($_POST['descripcion'] ?? ''), 
                'requiere_medico' => isset($_POST['requiere_medico']), 
                'documentos_requeridos' => trim($_POST['documentos_requeridos'] ?? ''), 
                'estado' => trim($_POST['estado'] ?? 'activo') 
            ];
            if (TipoServicio::store($data)) {
                $_SESSION['message'] = "Tipo de servicio creado exitosamente.";
                header("Location: index.php?route=tipos_servicios_index");
            } else {
                $_SESSION['error'] = "Error al crear el tipo de servicio. " . ($_SESSION['error_details'] ?? '');
                header("Location: index.php?route=tipos_servicios/create");
            }
            exit;
        }
    }

    public function edit($id = null) {
        check_permission('superusuario');
        $tipoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$tipoId) { 
            $_SESSION['error'] = "ID inválido."; 
            header("Location: index.php?route=tipos_servicios_index"); 
            exit; 
        }
        $tipoServicio = TipoServicio::getById($tipoId);
        if (!$tipoServicio) { 
            $_SESSION['error'] = "Tipo de servicio no encontrado."; 
            header("Location: index.php?route=tipos_servicios_index"); 
            exit; 
        }
        $pageTitle = 'Editar Tipo de Servicio';
        $currentRoute = 'tipos_servicios/edit';
        $contentView = __DIR__ . '/../views/tipos_servicios/edit.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function update() {
        check_permission('superusuario');
        if (isset($_POST['id_tipo_servicio'])) {
            $id = filter_input(INPUT_POST, 'id_tipo_servicio', FILTER_VALIDATE_INT);
            $data = [ 
                'nombre' => trim($_POST['nombre']), 
                'codigo_servicio' => trim($_POST['codigo_servicio'] ?? ''), 
                'descripcion' => trim($_POST['descripcion'] ?? ''), 
                'requiere_medico' => isset($_POST['requiere_medico']), 
                'documentos_requeridos' => trim($_POST['documentos_requeridos'] ?? ''), 
                'estado' => trim($_POST['estado'] ?? 'activo') 
            ];
            if (TipoServicio::update($id, $data)) {
                 $_SESSION['message'] = "Tipo de servicio actualizado exitosamente.";
            } else {
                  $_SESSION['error'] = "Error al actualizar. " . ($_SESSION['error_details'] ?? '');
            }
        }
        header("Location: index.php?route=tipos_servicios_index");
        exit;
    }

    public function delete($id = null) {
         check_permission('superusuario');
         $tipoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
         if ($tipoId) {
             if (TipoServicio::delete($tipoId)) {
                 $_SESSION['message'] = "Tipo de servicio eliminado exitosamente.";
             } else {
                  $_SESSION['error'] = "Error al eliminar. " . ($_SESSION['error_details'] ?? '');
             }
         }
         header("Location: index.php?route=tipos_servicios_index");
         exit;
    }
}