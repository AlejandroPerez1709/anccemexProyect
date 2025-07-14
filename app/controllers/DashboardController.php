<?php
// app/controllers/DashboardController.php
class DashboardController {
    public function index(){
    check_permission(); 
    
    $pageTitle = 'Dashboard';
    $currentRoute = 'dashboard';
    $contentView = __DIR__ . '/../views/dashboard/index.php';
    require_once __DIR__ . '/../views/layouts/master.php';
}
}