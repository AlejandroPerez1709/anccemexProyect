<!-- app/views/layouts/master.php     -->

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentRoute = $currentRoute ?? '';

// Lógica para determinar qué grupo de menú está activo
$isDashboardActive = ($currentRoute === 'dashboard');
$isServiciosActive = (strpos($currentRoute, 'servicios') === 0);
$isSociosActive = (strpos($currentRoute, 'socios') === 0);
$isEjemplaresActive = (strpos($currentRoute, 'ejemplares') === 0);
$isMedicosActive = (strpos($currentRoute, 'medicos') === 0);
$isEmpleadosActive = (strpos($currentRoute, 'empleados') === 0);
$isAdminSectionActive = strpos($currentRoute, 'usuarios') !== false || strpos($currentRoute, 'tipos_servicios') !== false;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="<?php echo BASE_URL; ?>/assets/img/logoAnccemex.png" alt="Logo" class="logo">
            <h1><?php echo APP_NAME; ?></h1>
        </div>
        <div class="user-info">
            <?php if(isset($_SESSION['user'])): ?>
                <span>
                    Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['user']['username']); ?></strong>
                     (<?php echo htmlspecialchars(ucfirst($_SESSION['user']['rol'])); ?>) |
                    <a href="index.php?route=logout">Cerrar sesión</a>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="layout-container">
        <div class="sidebar">
            <div class="sidebar-title">Módulos del Sistema</div>
            <ul class="sidebar-menu">
                <li><a href="index.php?route=dashboard" class="<?php echo $isDashboardActive ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="index.php?route=servicios_index" class="<?php echo $isServiciosActive ? 'active' : ''; ?>">Servicios</a></li>
                <li><a href="index.php?route=socios_index" class="<?php echo $isSociosActive ? 'active' : ''; ?>">Socios</a></li>
                <li><a href="index.php?route=ejemplares_index" class="<?php echo $isEjemplaresActive ? 'active' : ''; ?>">Ejemplares</a></li>
                <li><a href="index.php?route=medicos_index" class="<?php echo $isMedicosActive ? 'active' : ''; ?>">Médicos</a></li>
                <li><a href="index.php?route=empleados_index" class="<?php echo $isEmpleadosActive ? 'active' : ''; ?>">Empleados</a></li>
                <?php if(is_admin()): ?>
                <li class="has-submenu <?php echo $isAdminSectionActive ? 'open' : ''; ?>">
                    <a href="#">Administración</a>
                    <ul class="sidebar-submenu <?php echo $isAdminSectionActive ? 'visible' : ''; ?>">
                        <li><a href="index.php?route=usuarios_index" class="<?php echo (strpos($currentRoute, 'usuarios') !== false) ? 'active' : ''; ?>">Usuarios</a></li>
                        <li><a href="index.php?route=tipos_servicios_index" class="<?php echo (strpos($currentRoute, 'tipos_servicios') !== false) ? 'active' : ''; ?>">Tipos de Servicio</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="content">
            <?php
            // Mensajes globales (error, success, warning)
            $messageTypes = ['message' => 'alert-success', 'error' => 'alert-error', 'warning' => 'alert-warning'];
            foreach ($messageTypes as $key => $class) {
                if(isset($_SESSION[$key])){
                    echo "<div class='alert $class'>" . htmlspecialchars($_SESSION[$key]) . "</div>";
                    unset($_SESSION[$key]);
                }
            }
            
            // Incluir la vista de contenido
            if (isset($contentView) && file_exists($contentView)) {
                // Pasar variables a la vista
                if (isset($tiposServicios)) extract(['tiposServicios' => $tiposServicios]);
                if (isset($tipoServicio)) extract(['tipoServicio' => $tipoServicio]);
                if (isset($empleado)) extract(['empleado' => $empleado]);
                if (isset($usuario)) extract(['usuario' => $usuario]);
                if (isset($socio)) extract(['socio' => $socio]);
                if (isset($sociosList)) extract(['sociosList' => $sociosList]);
                if (isset($ejemplares)) extract(['ejemplares' => $ejemplares]);
                if (isset($ejemplar)) extract(['ejemplar' => $ejemplar]);
                if (isset($medicosList)) extract(['medicosList' => $medicosList]);
                if (isset($medico)) extract(['medico' => $medico]);
                if (isset($servicios)) extract(['servicios' => $servicios]);
                if (isset($servicio)) extract(['servicio' => $servicio]);
                if (isset($posiblesEstados)) extract(['posiblesEstados' => $posiblesEstados]);
                if (isset($formData)) extract(['formData' => $formData]);

                include $contentView;
            } else {
                echo "<div class='alert alert-error'>Error: No se pudo cargar la vista de contenido (" . htmlspecialchars($contentView ?? 'ruta no definida') . ").</div>";
                error_log("Error al cargar la vista: " . ($contentView ?? 'No definida') . " para la ruta: " . ($_GET['route'] ?? 'desconocida'));
            }
            ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.sidebar-menu .has-submenu > a');

            menuItems.forEach(item => {
                item.addEventListener('click', function(event) {
                    // Solo prevenir el default si es un submenu (Administración)
                    // y permitir que los otros enlaces naveguen directamente
                    const parentLi = this.parentElement;
                    if (parentLi.classList.contains('has-submenu')) {
                        event.preventDefault();
                    }
                    
                    // Cerrar otros submenús abiertos si se abre uno nuevo
                    if (parentLi.classList.contains('has-submenu') && !parentLi.classList.contains('open')) {
                        document.querySelectorAll('.sidebar-menu .has-submenu.open').forEach(openMenu => {
                            if (openMenu !== parentLi) {
                                openMenu.classList.remove('open');
                                openMenu.querySelector('.sidebar-submenu').classList.remove('visible');
                            }
                        });
                    }

                    // Toggle del submenú si es una pestaña con submenu
                    if (parentLi.classList.contains('has-submenu')) {
                        parentLi.classList.toggle('open');
                        const submenu = parentLi.querySelector('.sidebar-submenu');
                        if (submenu) {
                            submenu.classList.toggle('visible');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>