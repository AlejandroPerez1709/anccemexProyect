<?php
// app/controllers/AuditoriaController.php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once __DIR__ . '/../models/Auditoria.php';
require_once __DIR__ . '/../models/User.php'; // Para el filtro de usuarios
require_once __DIR__ . '/../../config/config.php';

class AuditoriaController {

    public function index() {
        check_permission('superusuario');

        // --- LÓGICA DE FILTROS ---
        $filters = [];
        if (!empty($_GET['filtro_usuario_id'])) {
            $filters['usuario_id'] = filter_input(INPUT_GET, 'filtro_usuario_id', FILTER_VALIDATE_INT);
        }
        if (!empty($_GET['filtro_fecha_inicio'])) {
            $filters['fecha_inicio'] = $_GET['filtro_fecha_inicio'];
        }
        if (!empty($_GET['filtro_fecha_fin'])) {
            $filters['fecha_fin'] = $_GET['filtro_fecha_fin'];
        }

        // --- LÓGICA DE PAGINACIÓN ---
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $records_per_page = 20; // Mostramos más registros en la bitácora
        $offset = ($page - 1) * $records_per_page;

        $total_records = Auditoria::countAll($filters);
        $total_pages = ceil($total_records / $records_per_page);

        // --- OBTENCIÓN DE DATOS ---
        $registros = Auditoria::getAll($filters, $records_per_page, $offset);
        $usuarios_list = User::getAll('', -1); // Para poblar el <select> de filtros

        // --- PREPARACIÓN DE LA VISTA ---
        $pageTitle = 'Bitácora de Auditoría';
        $currentRoute = 'auditoria_index';
        $contentView = __DIR__ . '/../views/auditoria/index.php';
        
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function exportToExcel() {
        check_permission('superusuario');

        // --- LÓGICA DE FILTROS ---
        $filters = [];
        if (!empty($_GET['filtro_usuario_id'])) {
            $filters['usuario_id'] = filter_input(INPUT_GET, 'filtro_usuario_id', FILTER_VALIDATE_INT);
        }
        if (!empty($_GET['filtro_fecha_inicio'])) {
            $filters['fecha_inicio'] = $_GET['filtro_fecha_inicio'];
        }
        if (!empty($_GET['filtro_fecha_fin'])) {
            $filters['fecha_fin'] = $_GET['filtro_fecha_fin'];
        }

        $registros = Auditoria::getAll($filters, -1); // -1 para obtener todos los registros

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->setCellValue('A1', 'ID Registro')
              ->setCellValue('B1', 'Fecha y Hora')
              ->setCellValue('C1', 'Usuario')
              ->setCellValue('D1', 'Acción')
              ->setCellValue('E1', 'Tipo de Entidad')
              ->setCellValue('F1', 'ID de Entidad')
              ->setCellValue('G1', 'Descripción Detallada');
        
        // Llenar datos
        $row = 2;
        foreach ($registros as $registro) {
            $sheet->setCellValue('A' . $row, $registro['id_auditoria'])
                  ->setCellValue('B' . $row, date('d/m/Y H:i:s', strtotime($registro['fecha_hora'])))
                  ->setCellValue('C' . $row, $registro['usuario_nombre'])
                  ->setCellValue('D' . $row, $registro['accion'])
                  ->setCellValue('E' . $row, $registro['tipo_entidad'])
                  ->setCellValue('F' . $row, $registro['id_entidad'])
                  ->setCellValue('G' . $row, $registro['descripcion']);
            $row++;
        }

        // Cabeceras para descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Auditoria.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}