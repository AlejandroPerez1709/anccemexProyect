<!-- app/views/layouts/master.php     -->

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentRoute = $currentRoute ?? '';

// Lógica para determinar qué grupo de menú está activo
$isAdminActive = strpos($currentRoute, 'usuarios') !== false || strpos($currentRoute, 'tipos_servicios') !== false;
$isEmpleadosActive = strpos($currentRoute, 'empleados') !== false;
$isMedicosActive = strpos($currentRoute, 'medicos') !== false;
$isSociosActive = strpos($currentRoute, 'socios') !== false;
$isEjemplaresActive = strpos($currentRoute, 'ejemplares') !== false;
// Se verifica que la ruta COMIENCE con 'servicios' para evitar la coincidencia con 'tipos_servicios'
$isServiciosActive = (strpos($currentRoute, 'servicios') === 0);

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
                     (<?php echo htmlspecialchars(ucfirst($_SESSION['user']['rol'])); ?>) | <a href="index.php?route=logout">Cerrar sesión</a>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="layout-container">
        <div class="sidebar">
            <div class="sidebar-title">Módulos del Sistema</div>
            <ul class="sidebar-menu">
                <li><a href="index.php?route=dashboard" class="<?php echo ($currentRoute === 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
                <hr>

                <?php if(is_admin()): ?>
                <li class="has-submenu <?php echo $isAdminActive ? 'open' : ''; ?>">
                    <a href="#">Administración</a>
                    <ul class="sidebar-submenu <?php echo $isAdminActive ? 'visible' : ''; ?>">
                        <li><a href="index.php?route=usuarios_index" class="<?php echo (strpos($currentRoute, 'usuarios') !== false) ? 'active' : ''; ?>">Usuarios</a></li>
                        <li><a href="index.php?route=tipos_servicios_index" class="<?php echo (strpos($currentRoute, 'tipos_servicios') !== false) ? 'active' : ''; ?>">Tipos de Servicio</a></li>
                    </ul>
                </li>
                <hr>
                <?php endif; ?>

                <li class="has-submenu <?php echo $isEmpleadosActive ? 'open' : ''; ?>">
                    <a href="#">Empleados</a>
                    <ul class="sidebar-submenu <?php echo $isEmpleadosActive ? 'visible' : ''; ?>">
                        <li><a href="index.php?route=empleados_index" class="<?php echo ($currentRoute === 'empleados_index' || $currentRoute === 'empleados/edit') ? 'active' : ''; ?>">Listado</a></li>
                        <li><a href="index.php?route=empleados/create" class="<?php echo ($currentRoute === 'empleados/create') ? 'active' : ''; ?>">Registrar Nuevo</a></li>
                    </ul>
                </li>

                <li class="has-submenu <?php echo $isMedicosActive ? 'open' : ''; ?>">
                    <a href="#">Médicos</a>
                    <ul class="sidebar-submenu <?php echo $isMedicosActive ? 'visible' : ''; ?>">
                        <li><a href="index.php?route=medicos_index" class="<?php echo ($currentRoute === 'medicos_index' || $currentRoute === 'medicos/edit') ? 'active' : ''; ?>">Listado</a></li>
                        <li><a href="index.php?route=medicos/create" class="<?php echo ($currentRoute === 'medicos/create') ? 'active' : ''; ?>">Registrar Nuevo</a></li>
                    </ul>
                </li>

                <li class="has-submenu <?php echo $isSociosActive ? 'open' : ''; ?>">
                    <a href="#">Socios</a>
                    <ul class="sidebar-submenu <?php echo $isSociosActive ? 'visible' : ''; ?>">
                        <li><a href="index.php?route=socios_index" class="<?php echo ($currentRoute === 'socios_index' || $currentRoute === 'socios/edit') ? 'active' : ''; ?>">Listado</a></li>
                        <li><a href="index.php?route=socios/create" class="<?php echo ($currentRoute === 'socios/create') ? 'active' : ''; ?>">Registrar Nuevo</a></li>
                    </ul>
                </li>

                <li class="has-submenu <?php echo $isEjemplaresActive ? 'open' : ''; ?>">
                    <a href="#">Ejemplares</a>
                    <ul class="sidebar-submenu <?php echo $isEjemplaresActive ? 'visible' : ''; ?>">
                        <li><a href="index.php?route=ejemplares_index" class="<?php echo ($currentRoute === 'ejemplares_index' || $currentRoute === 'ejemplares/edit') ? 'active' : ''; ?>">Listado</a></li>
                        <li><a href="index.php?route=ejemplares/create" class="<?php echo ($currentRoute === 'ejemplares/create') ? 'active' : ''; ?>">Registrar Nuevo</a></li>
                    </ul>
                </li>
                
                <li class="has-submenu <?php echo $isServiciosActive ? 'open' : ''; ?>">
                    <a href="#">Servicios</a>
                    <ul class="sidebar-submenu <?php echo $isServiciosActive ? 'visible' : ''; ?>">
                        <li><a href="index.php?route=servicios_index" class="<?php echo ($currentRoute === 'servicios_index' || $currentRoute === 'servicios/edit') ? 'active' : ''; ?>">Listado</a></li>
                        <li><a href="index.php?route=servicios/create" class="<?php echo ($currentRoute === 'servicios/create') ? 'active' : ''; ?>">Registrar Nuevo</a></li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class="content">
            <?php
            // Mensajes globales
            $messageTypes = ['message' => 'alert-success', 'error' => 'alert-error', 'warning' => 'alert-warning'];
            foreach ($messageTypes as $key => $class) {
                if(isset($_SESSION[$key])){
                    echo "<div class='alert $class'>" . $_SESSION[$key] . "</div>";
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

                include $contentView;
            } else {
                echo "<div class='alert alert-error'>Error: No se pudo cargar la vista de contenido.</div>";
                error_log("Error al cargar la vista: " . ($contentView ?? 'No definida'));
            }
            ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.sidebar-menu .has-submenu > a');

            menuItems.forEach(item => {
                item.addEventListener('click', function(event) {
                    event.preventDefault();
                    
                    const parentLi = this.parentElement;
                    
                    if (!parentLi.classList.contains('open')) {
                        document.querySelectorAll('.sidebar-menu .has-submenu.open').forEach(openMenu => {
                            if (openMenu !== parentLi) {
                                openMenu.classList.remove('open');
                                openMenu.querySelector('.sidebar-submenu').classList.remove('visible');
                            }
                        });
                    }

                    parentLi.classList.toggle('open');
                    const submenu = parentLi.querySelector('.sidebar-submenu');
                    if (submenu) {
                        submenu.classList.toggle('visible');
                    }
                });
            });
        });
    </script>
</body>
</html>