<?php
// app/views/layouts/master.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentRoute = $currentRoute ?? ''; // Asegura que $currentRoute exista
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
               
                <li><a href="index.php?route=dashboard" class="<?php echo ($currentRoute === 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>

                <?php if(isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'superusuario'): ?>
                    <li><a href="index.php?route=usuarios_index" class="<?php echo ($currentRoute === 'usuarios_index') ? 'active' : ''; ?>">Listado de Usuarios</a></li>
                    <li><a href="index.php?route=usuarios/create" class="<?php echo ($currentRoute === 'usuarios/create') ? 'active' : ''; ?>">Registrar Nuevo Usuario</a></li>
                <?php endif; ?>

                <?php if(isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'superusuario'): ?>

                     <li><a href="index.php?route=tipos_servicios_index" class="<?php echo ($currentRoute === 'tipos_servicios_index') ? 'active' : ''; ?>">Listado de Tipos de Servicios</a></li> 
                    <li><a href="index.php?route=tipos_servicios/create" class="<?php echo ($currentRoute === 'tipos_servicios/create') ? 'active' : ''; ?>">Registrar Nuevo Tipo de Servicio</a></li>

                <?php endif; ?>

                <hr style="margin: 5px 0; border-top: 1px solid #eee;"> 
                
                 <li><a href="index.php?route=empleados_index" class="<?php echo ($currentRoute === 'empleados_index') ? 'active' : ''; ?>">Listado de Empleados</a></li>
                <li><a href="index.php?route=empleados/create" class="<?php echo ($currentRoute === 'empleados/create') ? 'active' : ''; ?>">Registrar Nuevo Empleado</a></li>

                <?php if(isset($_SESSION['user'])): ?>
                     <li><a href="index.php?route=medicos_index" class="<?php echo ($currentRoute === 'medicos_index') ? 'active' : ''; ?>">Listado de Médicos</a></li>
                     <li><a href="index.php?route=medicos/create" class="<?php echo ($currentRoute === 'medicos/create') ? 'active' : ''; ?>">Registrar Nuevo Médico</a></li>
                <?php endif; ?>
                 

                <?php if(isset($_SESSION['user'])): ?>
                    <li><a href="index.php?route=socios_index" class="<?php echo ($currentRoute === 'socios_index') ? 'active' : ''; ?>">Listado de Socios</a></li>
                    <li><a href="index.php?route=socios/create" class="<?php echo ($currentRoute === 'socios/create') ? 'active' : ''; ?>">Registrar Nuevo Socio</a></li>
                     <?php endif; ?>

                <?php if(isset($_SESSION['user'])): ?>
                     <li><a href="index.php?route=ejemplares_index" class="<?php echo ($currentRoute === 'ejemplares_index') ? 'active' : ''; ?>">Listado de Ejemplares</a></li>
                     <li><a href="index.php?route=ejemplares/create" class="<?php echo ($currentRoute === 'ejemplares/create') ? 'active' : ''; ?>">Registrar Nuevo Ejemplar</a></li>
                      <?php endif; ?>

                

                <?php if(isset($_SESSION['user'])): ?>
                     <li><a href="index.php?route=servicios_index" class="<?php echo ($currentRoute === 'servicios_index') ? 'active' : ''; ?>">Listado de Servicios</a></li>
                     <li><a href="index.php?route=servicios/create" class="<?php echo ($currentRoute === 'servicios/create') ? 'active' : ''; ?>">Registrar Nuevo Servicio</a></li>
                <?php endif; ?>

                
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
            ?>

            <?php
            // Incluir la vista de contenido
             if (isset($contentView) && file_exists($contentView)) {
                // Pasar variables a la vista
                 if (isset($tiposServicios)) extract(['tiposServicios' => $tiposServicios]);
                 if (isset($tipoServicio)) extract(['tipoServicio' => $tipoServicio]);
                 if (isset($empleado)) extract(['empleado' => $empleado]);
                 if (isset($usuario)) extract(['usuario' => $usuario]);
                 if (isset($socio)) extract(['socio' => $socio]);
                 if (isset($sociosList)) extract(['sociosList' => $sociosList]);
                 if (isset($ejemplares)) extract(['ejemplares' => $ejemplares]); // Lista completa para JS
                 if (isset($ejemplar)) extract(['ejemplar' => $ejemplar]);
                 if (isset($medicosList)) extract(['medicosList' => $medicosList]);
                 if (isset($medico)) extract(['medico' => $medico]);
                 if (isset($servicios)) extract(['servicios' => $servicios]); // Lista para index
                 if (isset($servicio)) extract(['servicio' => $servicio]); // Datos para edit
                 if (isset($posiblesEstados)) extract(['posiblesEstados' => $posiblesEstados]); // Para edit dropdown

                include $contentView;
             } else {
                 echo "<div class='alert alert-error'>Error: No se pudo cargar la vista de contenido (" . htmlspecialchars($contentView ?? 'ruta no definida') . ").</div>";
                 error_log("Error al cargar la vista: " . ($contentView ?? 'No definida') . " para la ruta: " . ($_GET['route'] ?? 'desconocida'));
             }
            ?>
        </div>
    </div>
</body>
</html>