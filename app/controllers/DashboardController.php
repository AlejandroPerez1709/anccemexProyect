<?php
// app/controllers/DashboardController.php

require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Servicio.php';
// --- INICIO DE NUEVOS MODELOS A INCLUIR ---
require_once __DIR__ . '/../models/Ejemplar.php';
require_once __DIR__ . '/../models/Medico.php';
require_once __DIR__ . '/../models/Empleado.php';
// --- FIN DE NUEVOS MODELOS A INCLUIR ---

class DashboardController {
    public function index(){
        check_permission();
        
        // --- OBTENER TODOS LOS DATOS PARA EL DASHBOARD ---
        
        // Datos de Módulos para las nuevas tarjetas
        $totalSociosActivos = Socio::countActive();
        $totalEjemplaresActivos = Ejemplar::countActive();
        $totalMedicosActivos = Medico::countActive();
        $totalEmpleadosActivos = Empleado::countActive();

        // Datos de Servicios
        $totalServiciosActivos = Servicio::countActive();
        $statsServicios = Servicio::getDashboardStats();
        $serviciosRecientes = Servicio::getRecientes();
        $serviciosAtencion = Servicio::getAtencionRequerida();

        // --- PREPARAR DATOS PARA LA GRÁFICA DE DONA ---
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

        // --- PREPARAR DATOS PARA GRÁFICA DE LÍNEAS ---
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