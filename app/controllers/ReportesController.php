<?php
// app/controllers/ReportesController.php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once __DIR__ . '/../models/Servicio.php';
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Ejemplar.php';
require_once __DIR__ . '/../models/TipoServicio.php';
require_once __DIR__ . '/../../config/config.php';

class ReportesController {

    public function index() {
        check_permission('superusuario');

        $resultados = [];
        $filtros_aplicados = [];
        $tipo_reporte_generado = $_GET['tipo_reporte'] ?? 'servicios';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $records_per_page = 15;
        $offset = ($page - 1) * $records_per_page;
        $total_pages = 0;
        $total_records = 0;
        $datos_grafica = [];
        
        $socio_seleccionado = null;
        $resumen_servicios_socio = null;

        if (isset($_GET['tipo_reporte'])) {
            $filtros_aplicados = ($_GET['filtros'][$tipo_reporte_generado]) ?? [];

            switch ($tipo_reporte_generado) {
                case 'socios':
                    if (!empty($filtros_aplicados['socio_id'])) {
                        $socio_id = $filtros_aplicados['socio_id'];
                        $socio_seleccionado = Socio::getById($socio_id);
                        $todos_los_servicios = Servicio::getAll(['socio_id' => $socio_id], -1);
                        $total_records = count($todos_los_servicios);
                        $resultados = array_slice($todos_los_servicios, $offset, $records_per_page);
                        $estados_finalizados = ['Completado', 'Rechazado', 'Cancelado'];
                        $servicios_completados = 0;
                        foreach ($todos_los_servicios as $servicio) {
                            if (in_array($servicio['estado'], $estados_finalizados)) {
                                $servicios_completados++;
                            }
                        }
                        $resumen_servicios_socio = [
                            'en_proceso' => $total_records - $servicios_completados,
                            'completados' => $servicios_completados,
                            'total' => $total_records
                        ];
                        if ($total_records > 0) {
                            $estados_count = array_count_values(array_column($todos_los_servicios, 'estado'));
                            $datos_grafica = ['labels' => array_keys($estados_count), 'data' => array_values($estados_count)];
                        }
                    } else {
                        $total_records = Socio::countSociosParaReporte($filtros_aplicados);
                        $resultados = Socio::getSociosParaReporte($filtros_aplicados, $records_per_page, $offset);
                        if($total_records > 0) {
                            $todos_los_resultados = Socio::getSociosParaReporte($filtros_aplicados);
                            $activos = count(array_filter($todos_los_resultados, fn($s) => $s['estado'] === 'activo'));
                            $inactivos = count($todos_los_resultados) - $activos;
                            $datos_grafica = ['labels' => ['Activos', 'Inactivos'], 'data' => [$activos, $inactivos]];
                        }
                    }
                    break;

                case 'ejemplares':
                    $total_records = Ejemplar::countEjemplaresParaReporte($filtros_aplicados);
                    $resultados = Ejemplar::getEjemplaresParaReporte($filtros_aplicados, $records_per_page, $offset);
                    if($total_records > 0) {
                        $todos_los_resultados = Ejemplar::getEjemplaresParaReporte($filtros_aplicados);
                        $machos = count(array_filter($todos_los_resultados, fn($e) => $e['sexo'] === 'Macho'));
                        $hembras = count($todos_los_resultados) - $machos;
                        $datos_grafica = ['labels' => ['Machos', 'Hembras'], 'data' => [$machos, $hembras]];
                    }
                    break;
                
                // --- INICIO DE NUEVO CÓDIGO ---
                case 'servicios_resumen_mensual':
                    $resultados = Servicio::getMonthlyStats();
                    $total_records = count($resultados);
                    // Para este reporte no hay paginación, se muestran los 12 meses.
                    $records_per_page = $total_records > 0 ? $total_records : 1; 
                    $total_pages = 1;
                    if($total_records > 0) {
                        $datos_grafica = [
                            'labels' => array_column($resultados, 'mes'),
                            'data_creados' => array_column($resultados, 'creados'),
                            'data_completados' => array_column($resultados, 'completados')
                        ];
                    }
                    break;
                // --- FIN DE NUEVO CÓDIGO ---

                case 'servicios':
                default:
                    $total_records = Servicio::countServiciosParaReporte($filtros_aplicados);
                    $resultados = Servicio::getServiciosParaReporte($filtros_aplicados, $records_per_page, $offset);
                    if($total_records > 0) {
                        $todos_los_resultados = Servicio::getServiciosParaReporte($filtros_aplicados);
                        $estados_count = array_count_values(array_column($todos_los_resultados, 'estado'));
                        $datos_grafica = ['labels' => array_keys($estados_count), 'data' => array_values($estados_count)];
                    }
                    break;
            }
            // Evita recalcular si ya se definió en el caso 'servicios_resumen_mensual'
            if ($tipo_reporte_generado !== 'servicios_resumen_mensual') {
                $total_pages = ceil($total_records / $records_per_page);
            }
        }

        $tiposServicioList = TipoServicio::getActiveForSelect();
        $sociosList = Socio::getActiveSociosForSelect();
        $posiblesEstadosServicio = ['Pendiente Docs/Pago', 'Recibido Completo', 'Pendiente Visita Medico', 'Pendiente Resultado Lab', 'Enviado a LG', 'Pendiente Respuesta LG', 'Completado', 'Rechazado', 'Cancelado'];
        $posiblesEstadosGenerales = ['activo', 'inactivo'];
        $posiblesSexos = ['Macho', 'Hembra'];

        $pageTitle = 'Módulo de Reportes';
        $currentRoute = 'reportes';
        $contentView = __DIR__ . '/../views/reportes/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function export() {
        check_permission('superusuario');

        $request_data = $_GET;
        $tipo_reporte = $request_data['tipo_reporte'] ?? 'servicios';
        $filtros = ($request_data['filtros'][$tipo_reporte]) ?? [];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $filename = "Reporte.xlsx";

        switch ($tipo_reporte) {
            case 'socios':
                if (!empty($filtros['socio_id'])) {
                    $resultados = Servicio::getAll(['socio_id' => $filtros['socio_id']], -1);
                    $socio = Socio::getById($filtros['socio_id']);
                    $filename = "Reporte_Servicios_Socio_" . ($socio['codigoGanadero'] ?? $filtros['socio_id']) . ".xlsx";
                    $sheet->setCellValue('A1', 'N° Servicio')->setCellValue('B1', 'Tipo Servicio')->setCellValue('C1', 'Socio')->setCellValue('D1', 'Ejemplar')->setCellValue('E1', 'Estado')->setCellValue('F1', 'Fecha Solicitud')->setCellValue('G1', 'Fecha Finalización');
                    $row = 2;
                    foreach ($resultados as $servicio) {
                        $sheet->setCellValue('A' . $row, $servicio['id_servicio'])->setCellValue('B' . $row, $servicio['tipo_servicio_nombre'])->setCellValue('C' . $row, $servicio['socio_nombre'] . ' ' . $servicio['socio_apPaterno'])->setCellValue('D' . $row, $servicio['ejemplar_nombre'])->setCellValue('E' . $row, $servicio['estado'])->setCellValue('F' . $row, !empty($servicio['fechaSolicitud']) ? date('d/m/Y', strtotime($servicio['fechaSolicitud'])) : '-')->setCellValue('G' . $row, !empty($servicio['fechaFinalizacion']) ? date('d/m/Y', strtotime($servicio['fechaFinalizacion'])) : '-');
                        $row++;
                    }
                } else {
                    $resultados = Socio::getSociosParaReporte($filtros);
                    $filename = "Reporte_de_Socios.xlsx";
                    $sheet->setCellValue('A1', 'N° Socio')->setCellValue('B1', 'Nombre Titular')->setCellValue('C1', 'Apellidos')->setCellValue('D1', 'Nombre Ganadería')->setCellValue('E1', 'Cód. Ganadero')->setCellValue('F1', 'Email')->setCellValue('G1', 'Teléfono')->setCellValue('H1', 'RFC')->setCellValue('I1', 'Fecha Registro')->setCellValue('J1', 'Estado');
                    $row = 2;
                    foreach ($resultados as $socio) {
                        $sheet->setCellValue('A' . $row, $socio['id_socio'])->setCellValue('B' . $row, $socio['nombre'])->setCellValue('C' . $row, $socio['apellido_paterno'] . ' ' . $socio['apellido_materno'])->setCellValue('D' . $row, $socio['nombre_ganaderia'])->setCellValue('E' . $row, $socio['codigoGanadero'])->setCellValue('F' . $row, $socio['email'])->setCellValue('G' . $row, $socio['telefono'])->setCellValue('H' . $row, $socio['identificacion_fiscal_titular'])->setCellValue('I' . $row, !empty($socio['fechaRegistro']) ? date('d/m/Y', strtotime($socio['fechaRegistro'])) : '-')->setCellValue('J' . $row, ucfirst($socio['estado']));
                        $row++;
                    }
                }
                break;

            case 'ejemplares':
                $resultados = Ejemplar::getEjemplaresParaReporte($filtros);
                $filename = "Reporte_de_Ejemplares.xlsx";
                $sheet->setCellValue('A1', 'N° Ejemplar')->setCellValue('B1', 'Nombre')->setCellValue('C1', 'Cód. Ejemplar')->setCellValue('D1', 'Socio Propietario')->setCellValue('E1', 'Cód. Ganadero')->setCellValue('F1', 'Sexo')->setCellValue('G1', 'Fecha Nacimiento')->setCellValue('H1', 'Raza')->setCellValue('I1', 'Estado');
                $row = 2;
                foreach ($resultados as $ejemplar) {
                    $sheet->setCellValue('A' . $row, $ejemplar['id_ejemplar'])->setCellValue('B' . $row, $ejemplar['nombre'])->setCellValue('C' . $row, $ejemplar['codigo_ejemplar'])->setCellValue('D' . $row, $ejemplar['nombre_socio'])->setCellValue('E' . $row, $ejemplar['socio_codigo_ganadero'])->setCellValue('F' . $row, $ejemplar['sexo'])->setCellValue('G' . $row, !empty($ejemplar['fechaNacimiento']) ? date('d/m/Y', strtotime($ejemplar['fechaNacimiento'])) : '-')->setCellValue('H' . $row, $ejemplar['raza'])->setCellValue('I' . $row, ucfirst($ejemplar['estado']));
                    $row++;
                }
                break;

            // --- INICIO DE NUEVO CÓDIGO ---
            case 'servicios_resumen_mensual':
                $resultados = Servicio::getMonthlyStats();
                $filename = "Reporte_Resumen_Mensual_Servicios.xlsx";
                $sheet->setCellValue('A1', 'Mes')->setCellValue('B1', 'Servicios Creados')->setCellValue('C1', 'Servicios Completados');
                $row = 2;
                foreach ($resultados as $mes) {
                    $sheet->setCellValue('A' . $row, $mes['mes'])
                          ->setCellValue('B' . $row, $mes['creados'])
                          ->setCellValue('C' . $row, $mes['completados']);
                    $row++;
                }
                break;
            // --- FIN DE NUEVO CÓDIGO ---

            case 'servicios':
            default:
                $resultados = Servicio::getServiciosParaReporte($filtros);
                $filename = "Reporte_de_Servicios.xlsx";
                $sheet->setCellValue('A1', 'N° Servicio')->setCellValue('B1', 'Tipo Servicio')->setCellValue('C1', 'Socio')->setCellValue('D1', 'Ejemplar')->setCellValue('E1', 'Estado')->setCellValue('F1', 'Fecha Solicitud')->setCellValue('G1', 'Fecha Finalización');
                $row = 2;
                foreach ($resultados as $servicio) {
                    $sheet->setCellValue('A' . $row, $servicio['id_servicio'])->setCellValue('B' . $row, $servicio['tipo_servicio_nombre'])->setCellValue('C' . $row, $servicio['socio_nombre'] . ' ' . $servicio['socio_apPaterno'])->setCellValue('D' . $row, $servicio['ejemplar_nombre'])->setCellValue('E' . $row, $servicio['estado'])->setCellValue('F' . $row, !empty($servicio['fechaSolicitud']) ? date('d/m/Y', strtotime($servicio['fechaSolicitud'])) : '-')->setCellValue('G' . $row, !empty($servicio['fechaFinalizacion']) ? date('d/m/Y', strtotime($servicio['fechaFinalizacion'])) : '-');
                    $row++;
                }
                break;
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}