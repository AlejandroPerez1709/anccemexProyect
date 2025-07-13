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

        // --- Rutas de Usuarios (Admin) ---
        $this->addRoute('usuarios_index', 'UsuariosController@index');
        $this->addRoute('usuarios/create', 'UsuariosController@create');
        $this->addRoute('usuarios_store', 'UsuariosController@store');
        $this->addRoute('usuarios/edit', 'UsuariosController@edit');
        $this->addRoute('usuarios_update', 'UsuariosController@update');
        $this->addRoute('usuarios_delete', 'UsuariosController@delete');

        // --- Rutas de Socios ---
        $this->addRoute('socios_index', 'SociosController@index');
        $this->addRoute('socios/create', 'SociosController@create');
        $this->addRoute('socios_store', 'SociosController@store');
        $this->addRoute('socios/edit', 'SociosController@edit');
        $this->addRoute('socios_update', 'SociosController@update');
        $this->addRoute('socios_delete', 'SociosController@delete');

        // --- Rutas de Ejemplares ---
        $this->addRoute('ejemplares_index', 'EjemplaresController@index');
        $this->addRoute('ejemplares/create', 'EjemplaresController@create');
        $this->addRoute('ejemplares_store', 'EjemplaresController@store');
        $this->addRoute('ejemplares/edit', 'EjemplaresController@edit');
        $this->addRoute('ejemplares_update', 'EjemplaresController@update');
        $this->addRoute('ejemplares_delete', 'EjemplaresController@delete');

        // --- Rutas de Médicos ---
        $this->addRoute('medicos_index', 'MedicosController@index');
        $this->addRoute('medicos/create', 'MedicosController@create');
        $this->addRoute('medicos_store', 'MedicosController@store');
        $this->addRoute('medicos/edit', 'MedicosController@edit');
        $this->addRoute('medicos_update', 'MedicosController@update');
        $this->addRoute('medicos_delete', 'MedicosController@delete');

         // --- Rutas de Tipos de Servicio (Admin) ---
         $this->addRoute('tipos_servicios_index', 'TiposServiciosController@index');
         $this->addRoute('tipos_servicios/create', 'TiposServiciosController@create');
         $this->addRoute('tipos_servicios_store', 'TiposServiciosController@store');
         $this->addRoute('tipos_servicios/edit', 'TiposServiciosController@edit');
         $this->addRoute('tipos_servicios_update', 'TiposServiciosController@update');
         $this->addRoute('tipos_servicios_delete', 'TiposServiciosController@delete');

         // --- Rutas de Servicios (Trámites) ---
         $this->addRoute('servicios_index', 'ServiciosController@index');
         $this->addRoute('servicios/create', 'ServiciosController@create');
         $this->addRoute('servicios_store', 'ServiciosController@store');
         $this->addRoute('servicios/edit', 'ServiciosController@edit');
         $this->addRoute('servicios_update', 'ServiciosController@update');
         $this->addRoute('servicios_cancel', 'ServiciosController@cancel');

         // --- Rutas de Documentos ---
         $this->addRoute('documento_download', 'DocumentosController@download'); // Descargar documento
         $this->addRoute('documento_delete', 'DocumentosController@delete');    // Eliminar documento
         

        // Añadir más rutas aquí...
    }

    /**
     * Añade una ruta al array de rutas.
     * @param string $route La URL (ej. 'usuarios/create')
     * @param string $handler El 'Controlador@metodo'
     */
    public function addRoute($route, $handler) {
        $this->routes[$route] = $handler;
    }

    /**
     * Procesa la solicitud actual basada en la URL.
     */
    public function route() {
        // Obtener la ruta solicitada del parámetro GET, limpiando barras iniciales/finales
        $requestedRoute = trim($_GET['route'] ?? '', '/');

        // Determinar ruta por defecto si está vacía
        if (empty($requestedRoute)) {
            // Iniciar sesión si no está iniciada para chequear el usuario
            if (session_status() === PHP_SESSION_NONE) {
                 session_start();
            }
            // Redirigir a dashboard si está logueado, sino a login
            if(isset($_SESSION['user'])){
                 $requestedRoute = 'dashboard';
            } else {
                 $requestedRoute = 'login';
            }
        }

        // Buscar la ruta en nuestro array de rutas definidas
        if (array_key_exists($requestedRoute, $this->routes)) {
            // Obtener el manejador 'Controlador@metodo'
            $handler = $this->routes[$requestedRoute];
            // Separar el nombre del controlador y el nombre del método
            list($controllerName, $methodName) = explode('@', $handler);

            // Construir la ruta al archivo del controlador
            $controllerFile = __DIR__ . '/../app/controllers/' . $controllerName . '.php';

            // Verificar si el archivo del controlador existe
            if (file_exists($controllerFile)) {
                require_once $controllerFile; // Incluir el archivo del controlador

                // Verificar si la clase del controlador existe
                if (class_exists($controllerName)) {
                    // Crear una instancia del controlador
                    $controllerInstance = new $controllerName();

                    // Verificar si el método existe en la instancia del controlador
                    if (method_exists($controllerInstance, $methodName)) {
                        // Preparar parámetros (simple, solo para ID por ahora)
                        $params = [];
                        // Pasar el 'id' como parámetro si existe en GET y es un número
                        if(isset($_GET['id'])) {
                            $idParam = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
                            if ($idParam !== false && $idParam !== null) {
                                $params[] = $idParam;
                            } else {
                                // Opcional: Manejar ID inválido (ej. loguear, mostrar error, etc.)
                                error_log("Advertencia: Se recibió un ID inválido en la ruta '$requestedRoute'");
                            }
                        }

                         // Llamar al método del controlador con los parámetros extraídos
                         // call_user_func_array llama a un método de un objeto con un array de parámetros
                         call_user_func_array([$controllerInstance, $methodName], $params);

                    } else {
                        // Error: Método no encontrado en el controlador
                        $this->notFound("Método no encontrado en el controlador: " . htmlspecialchars($methodName));
                    }
                } else {
                    // Error: Clase controladora no encontrada
                    $this->notFound("Clase controladora no encontrada: " . htmlspecialchars($controllerName));
                }
            } else {
                // Error: Archivo del controlador no encontrado
                $this->notFound("Archivo controlador no encontrado: " . htmlspecialchars($controllerFile));
            }
        } else {
            // Error: Ruta no definida en el array $routes
            $this->notFound("Ruta no definida en el router: " . htmlspecialchars($requestedRoute));
        }
    }

    /**
     * Muestra una página de error 404 simple y detiene la ejecución.
     */
    protected function notFound($message = "Página no encontrada") {
        http_response_code(404); // Establecer código de estado HTTP 404
        // Loguear el error para depuración interna
        error_log("Routing Error 404: " . $message . " (Ruta solicitada: " . ($_GET['route'] ?? 'N/A') . ")");

        // Mostrar un mensaje amigable al usuario
        // (Podrías crear una vista específica para errores 404)
        echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>Error 404</title></head><body>";
        echo "<h2>Error 404 - Página no encontrada</h2>";
        echo "<p>Lo sentimos, la página que buscas no existe o no está disponible.</p>";
        echo "<p><i>Detalle técnico: " . htmlspecialchars($message) . "</i></p>";

        // Enlace para volver, dependiendo si hay sesión o no
        $goBackLink = 'index.php?route=login';
         // Asegurar que la sesión esté iniciada para verificar el usuario
         if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if(isset($_SESSION['user'])){ $goBackLink = 'index.php?route=dashboard'; }

        echo "<p><a href='$goBackLink'>Volver al inicio</a></p>";
        echo "</body></html>";
        exit; // Detener la ejecución del script
    }
} // Fin clase Router
?>