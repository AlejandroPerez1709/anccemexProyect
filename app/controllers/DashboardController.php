<?php
// app/controllers/DashboardController.php

// AÑADIR ESTAS LÍNEAS PARA USAR LOS MODELOS
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Servicio.php';

class DashboardController {
    public function index(){
        check_permission(); 
        
        // OBTENER LOS DATOS REALES
        $totalSociosActivos = Socio::countActive();
        $totalServiciosActivos = Servicio::countActive();

        $pageTitle = 'Dashboard';
        $currentRoute = 'dashboard';
        $contentView = __DIR__ . '/../views/dashboard/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }
}