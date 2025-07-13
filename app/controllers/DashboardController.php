<?php
// app/controllers/DashboardController.php
class DashboardController {
    public function index(){
        session_start();
        if(!isset($_SESSION['user'])){
            header("Location: index.php?route=login");
            exit;
        }
        
        $pageTitle = 'Dashboard';
        $currentRoute = 'dashboard';
        $contentView = __DIR__ . '/../views/dashboard/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }
}