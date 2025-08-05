<?php
// app/controllers/ReportesController.php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once __DIR__ . '/../models/Servicio.php';
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/TipoServicio.php';
require_once __DIR__ . '/../../config/config.php';

class ReportesController {

    /**
     * Muestra la página principal del módulo de reportes y maneja la generación de reportes.
     */
    public function index() {
        check_permission('superusuario');

        $resultados = [];
        $filtros_aplicados = [];

        // Verificar si se está generando un reporte (vía POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filtros = $_POST['filtros'] ?? [];
            $accion = $_POST['accion'] ?? 'generar';

            // Limpiar y validar filtros
            $filtros_aplicados = [
                'fecha_inicio' => !empty($filtros['fecha_inicio']) ? $filtros['fecha_inicio'] : null,
                'fecha_fin' => !empty($filtros['fecha_fin']) ? $filtros['fecha_fin'] : null,
                'estado' => !empty($filtros['estado']) ? $filtros['estado'] : null,
                'tipo_servicio_id' => !empty($filtros['tipo_servicio_id']) ? filter_var($filtros['tipo_servicio_id'], FILTER_VALIDATE_INT) : null,
            ];

            // Obtener los datos para el reporte
            $resultados = Servicio::getServiciosParaReporte($filtros_aplicados);

            // Si la acción es exportar, llamar a la función de exportación
            if ($accion === 'exportar') {
                $this->exportarReporteServicios($resultados);
                exit; // Detener la ejecución para que solo se descargue el archivo
            }
        }

        // Preparar datos para los menús desplegables de los filtros
        $tiposServicioList = TipoServicio::getActiveForSelect();
        $sociosList = Socio::getActiveSociosForSelect();
        $posiblesEstados = [
            'Pendiente Docs/Pago', 'Recibido Completo', 'Pendiente Visita Medico', 
            'Pendiente Resultado Lab', 'Enviado a LG', 'Pendiente Respuesta LG', 
            'Completado', 'Rechazado', 'Cancelado'
        ];

        $pageTitle = 'Módulo de Reportes';
        $currentRoute = 'reportes';
        $contentView = __DIR__ . '/../views/reportes/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Genera y descarga un archivo Excel con los resultados de un reporte de servicios.
     * @param array $resultados Los datos del reporte a exportar.
     */
    private function exportarReporteServicios($resultados) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->setCellValue('A1', 'N° Servicio')
              ->setCellValue('B1', 'Tipo Servicio')
              ->setCellValue('C1', 'Socio')
              ->setCellValue('D1', 'Ejemplar')
              ->setCellValue('E1', 'Estado')
              ->setCellValue('F1', 'Fecha Solicitud')
              ->setCellValue('G1', 'Fecha Finalización');

        // Llenar datos
        $row = 2;
        foreach ($resultados as $servicio) {
            $sheet->setCellValue('A' . $row, $servicio['id_servicio'])
                  ->setCellValue('B' . $row, $servicio['tipo_servicio_nombre'])
                  ->setCellValue('C' . $row, $servicio['socio_nombre'] . ' ' . $servicio['socio_apPaterno'])
                  ->setCellValue('D' . $row, $servicio['ejemplar_nombre'])
                  ->setCellValue('E' . $row, $servicio['estado'])
                  ->setCellValue('F' . $row, !empty($servicio['fechaSolicitud']) ? date('d/m/Y', strtotime($servicio['fechaSolicitud'])) : '-')
                  ->setCellValue('G' . $row, !empty($servicio['fechaFinalizacion']) ? date('d/m/Y', strtotime($servicio['fechaFinalizacion'])) : '-');
            $row++;
        }

        // Cabeceras para la descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_de_Servicios.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }
}