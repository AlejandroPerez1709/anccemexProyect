<?php
// app/controllers/DashboardController.php

require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Servicio.php';
require_once __DIR__ . '/../models/Ejemplar.php';
require_once __DIR__ . '/../models/Medico.php';
require_once __DIR__ . '/../models/Empleado.php';

class DashboardController {
    public function index(){
        check_permission();

        // --- INICIO DE NUEVA LÓGICA DE FILTROS DE FECHA ---
        $periodo = $_GET['periodo'] ?? 'all_time';
        $filtros_fecha = [];
        $titulo_kpi_nuevos = "Activos (Total)";
        $titulo_kpi_servicios = "Completados (Mes Actual)";

        switch ($periodo) {
            case 'mes':
                $filtros_fecha['fecha_inicio'] = date('Y-m-01');
                $filtros_fecha['fecha_fin'] = date('Y-m-t');
                $titulo_kpi_nuevos = "Nuevos (Este Mes)";
                $titulo_kpi_servicios = "Completados (Este Mes)";
                break;
            case 'trimestre':
                $month = date('n');
                if ($month <= 3) {
                    $filtros_fecha['fecha_inicio'] = date('Y-01-01');
                    $filtros_fecha['fecha_fin'] = date('Y-03-31');
                } elseif ($month <= 6) {
                    $filtros_fecha['fecha_inicio'] = date('Y-04-01');
                    $filtros_fecha['fecha_fin'] = date('Y-06-30');
                } elseif ($month <= 9) {
                    $filtros_fecha['fecha_inicio'] = date('Y-07-01');
                    $filtros_fecha['fecha_fin'] = date('Y-09-30');
                } else {
                    $filtros_fecha['fecha_inicio'] = date('Y-10-01');
                    $filtros_fecha['fecha_fin'] = date('Y-12-31');
                }
                $titulo_kpi_nuevos = "Nuevos (Este Trimestre)";
                $titulo_kpi_servicios = "Completados (Trimestre)";
                break;
            case 'anio':
                $filtros_fecha['fecha_inicio'] = date('Y-01-01');
                $filtros_fecha['fecha_fin'] = date('Y-12-31');
                $titulo_kpi_nuevos = "Nuevos (Este Año)";
                $titulo_kpi_servicios = "Completados (Este Año)";
                break;
        }
        // --- FIN DE NUEVA LÓGICA DE FILTROS DE FECHA ---

        // --- OBTENER TODOS LOS DATOS PARA EL DASHBOARD (AHORA CON FILTROS) ---
        $totalSocios = Socio::countActive($filtros_fecha);
        $totalEjemplares = Ejemplar::countActive($filtros_fecha);
        $totalMedicos = Medico::countActive($filtros_fecha);
        $totalEmpleados = Empleado::countActive($filtros_fecha);

        $totalServiciosActivos = Servicio::countActive($filtros_fecha);
        $statsServicios = Servicio::getDashboardStats($filtros_fecha);
        $serviciosRecientes = Servicio::getRecientes(); // Se mantiene como actividad global reciente
        $serviciosAtencion = Servicio::getAtencionRequerida(); // Se mantiene como alerta global

        // --- PREPARAR DATOS PARA LA GRÁFICA DE DONA (AHORA FILTRADA) ---
        $doughnutChartLabels = [];
        $doughnutChartData = [];
        if (!empty($statsServicios['distribucion_estados'])) {
            foreach ($statsServicios['distribucion_estados'] as $estado) {
                $doughnutChartLabels[] = $estado['estado'];
                $doughnutChartData[] = $estado['total'];
            }
        }
        $doughnutChartLabelsJSON = json_encode($doughnutChartLabels);
        $doughnutChartDataJSON = json_encode($doughnutChartData);

        // --- PREPARAR DATOS PARA GRÁFICA DE LÍNEAS (SE MANTIENE ÚLTIMOS 12 MESES) ---
        $monthlyStats = Servicio::getMonthlyStats();
        $lineChartLabels = [];
        $lineChartCreadosData = [];
        $lineChartCompletadosData = [];
        foreach ($monthlyStats as $stat) {
            $lineChartLabels[] = $stat['mes'];
            $lineChartCreadosData[] = $stat['creados'];
            $lineChartCompletadosData[] = $stat['completados'];
        }
        $lineChartLabelsJSON = json_encode($lineChartLabels);
        $lineChartCreadosJSON = json_encode($lineChartCreadosData);
        $lineChartCompletadosJSON = json_encode($lineChartCompletadosData);

        // --- CARGAR LA VISTA Y PASARLE LOS DATOS ---
        $pageTitle = 'Dashboard';
        $currentRoute = 'dashboard';
        $contentView = __DIR__ . '/../views/dashboard/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }
}