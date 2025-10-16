<?php
// core/Router.php

class Router {
    protected $routes = [];
    protected $controller = ''; 
    protected $method = '';
    protected $params = [];

    public function __construct() {
        // --- Rutas de Autenticación ---
        $this->addRoute('login', 'AuthController@login');
        $this->addRoute('authenticate', 'AuthController@authenticate');
        $this->addRoute('logout', 'AuthController@logout');

        // --- Rutas del Dashboard ---
        $this->addRoute('dashboard', 'DashboardController@index');

        // --- Rutas de Empleados ---
        $this->addRoute('empleados_index', 'EmpleadosController@index');
        $this->addRoute('empleados/create', 'EmpleadosController@create');
        $this->addRoute('empleados_store', 'EmpleadosController@store');
        $this->addRoute('empleados/edit', 'EmpleadosController@edit');
        $this->addRoute('empleados_update', 'EmpleadosController@update');
        $this->addRoute('empleados_delete', 'EmpleadosController@delete');
        $this->addRoute('empleados_export_excel', 'EmpleadosController@exportToExcel');

        // --- Rutas de Usuarios (Admin) ---
        $this->addRoute('usuarios_index', 'UsuariosController@index');
        $this->addRoute('usuarios/create', 'UsuariosController@create');
        $this->addRoute('usuarios_store', 'UsuariosController@store');
        $this->addRoute('usuarios/edit', 'UsuariosController@edit');
        $this->addRoute('usuarios_update', 'UsuariosController@update');
        $this->addRoute('usuarios_delete', 'UsuariosController@delete');
        $this->addRoute('usuarios_export_excel', 'UsuariosController@exportToExcel');

        // --- Rutas de Socios ---
        $this->addRoute('socios_index', 'SociosController@index');
        $this->addRoute('socios/create', 'SociosController@create');
        $this->addRoute('socios_store', 'SociosController@store');
        $this->addRoute('socios/edit', 'SociosController@edit');
        $this->addRoute('socios_update', 'SociosController@update');
        $this->addRoute('socios_delete', 'SociosController@delete');
        $this->addRoute('socios_export_excel', 'SociosController@exportToExcel');

        // --- Rutas de Ejemplares ---
        $this->addRoute('ejemplares_index', 'EjemplaresController@index');
        $this->addRoute('ejemplares/create', 'EjemplaresController@create');
        $this->addRoute('ejemplares_store', 'EjemplaresController@store');
        $this->addRoute('ejemplares/edit', 'EjemplaresController@edit');
        $this->addRoute('ejemplares_update', 'EjemplaresController@update');
        $this->addRoute('ejemplares_delete', 'EjemplaresController@delete');
        $this->addRoute('ejemplares_export_excel', 'EjemplaresController@exportToExcel');
        $this->addRoute('ejemplares_por_socio', 'EjemplaresController@getPorSocio'); // <-- NUEVA RUTA AÑADIDA

        // --- Rutas de Médicos ---
        $this->addRoute('medicos_index', 'MedicosController@index');
        $this->addRoute('medicos/create', 'MedicosController@create');
        $this->addRoute('medicos_store', 'MedicosController@store');
        $this->addRoute('medicos/edit', 'MedicosController@edit');
        $this->addRoute('medicos_update', 'MedicosController@update');
        $this->addRoute('medicos_delete', 'MedicosController@delete');
        $this->addRoute('medicos_export_excel', 'MedicosController@exportToExcel');

         // --- Rutas de Tipos de Servicio (Admin) ---
         $this->addRoute('tipos_servicios_index', 'TiposServiciosController@index');
         $this->addRoute('tipos_servicios/create', 'TiposServiciosController@create');
         $this->addRoute('tipos_servicios_store', 'TiposServiciosController@store');
         $this->addRoute('tipos_servicios/edit', 'TiposServiciosController@edit');
         $this->addRoute('tipos_servicios_update', 'TiposServiciosController@update');
         $this->addRoute('tipos_servicios_delete', 'TiposServiciosController@delete');
         $this->addRoute('tipos_servicios_export_excel', 'TiposServiciosController@exportToExcel');

         // --- Rutas de Servicios (Trámites) ---
         $this->addRoute('servicios_index', 'ServiciosController@index');
         $this->addRoute('servicios/create', 'ServiciosController@create');
         $this->addRoute('servicios_store', 'ServiciosController@store');
         $this->addRoute('servicios/edit', 'ServiciosController@edit');
         $this->addRoute('servicios_update', 'ServiciosController@update');
         $this->addRoute('servicios_cancel', 'ServiciosController@cancel');
         $this->addRoute('servicios_export_excel', 'ServiciosController@exportToExcel');
         $this->addRoute('servicios_update_status', 'ServiciosController@updateStatus');
         $this->addRoute('servicios_get_valid_states', 'ServiciosController@getValidNextStates');

         // --- Rutas de Reportes ---
         $this->addRoute('reportes', 'ReportesController@index');
         $this->addRoute('reportes_export', 'ReportesController@export');

         // --- NUEVAS RUTAS DE AUDITORÍA ---
         $this->addRoute('auditoria_index', 'AuditoriaController@index');
         $this->addRoute('auditoria_export_excel', 'AuditoriaController@exportToExcel');

         // --- Rutas de Documentos ---
         $this->addRoute('documento_download', 'DocumentosController@download');
         $this->addRoute('documento_delete', 'DocumentosController@delete');
    }

    public function addRoute($route, $handler) {
        $this->routes[$route] = $handler;
    }

    public function route() {
        $requestedRoute = trim($_GET['route'] ?? '', '/');
        if (empty($requestedRoute)) {
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            $requestedRoute = isset($_SESSION['user']) ? 'dashboard' : 'login';
        }

        if (array_key_exists($requestedRoute, $this->routes)) {
            list($controllerName, $methodName) = explode('@', $this->routes[$requestedRoute]);
            $controllerFile = __DIR__ . '/../app/controllers/' . $controllerName . '.php';

            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                if (class_exists($controllerName)) {
                    $controllerInstance = new $controllerName();
                    if (method_exists($controllerInstance, $methodName)) {
                        $params = [];
                        if(isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT) !== false) {
                            $params[] = (int)$_GET['id'];
                        }
                         call_user_func_array([$controllerInstance, $methodName], $params);
                    } else { $this->notFound("Método no encontrado: " . $methodName); }
                    
                } else { $this->notFound("Clase no encontrada: " . $controllerName); }

            } else { $this->notFound("Archivo controlador no encontrado: " . $controllerFile); }

        } else { $this->notFound("Ruta no definida: " . $requestedRoute); }
    }

    protected function notFound($message = "Página no encontrada") {
        http_response_code(404);
        error_log("Routing Error 404: " . $message);
        echo "Error 404: " . htmlspecialchars($message);
        exit;
    }
}