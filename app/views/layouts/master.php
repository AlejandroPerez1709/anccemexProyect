<?php
//app/views/layouts/master.php

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
$isAdminSectionActive = strpos($currentRoute, 'usuarios') !== false || strpos($currentRoute, 'tipos_servicios') !== false || strpos($currentRoute, 'reportes') !== false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <img src="<?php echo BASE_URL; ?>/assets/img/logoAnccemex.png" alt="Logo" class="logo">
        </div>
        <div class="header-center">
             <h1 id="header-title"><?php echo APP_NAME; ?></h1>
        </div>
        <div class="header-right">
            <div class="user-info">
                <?php if(isset($_SESSION['user'])): ?>
                    <span>
                        Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['user']['nombre'] . ' ' . $_SESSION['user']['apellido_paterno']); ?></strong>
                    </span>
                    
                    <a href="index.php?route=logout" class="btn-logout">
                         <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M6.26489 3.80698L7.41191 5.44558C5.34875 6.89247 4 9.28873 4 12C4 16.4183 7.58172 20 12 20C16.4183 20 20 16.4183 20 12C20 9.28873 18.6512 6.89247 16.5881 5.44558L17.7351 3.80698C20.3141 5.61559 22 8.61091 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 8.61091 3.68594 5.61559 6.26489 3.80698ZM11 12V2H13V12H11Z"></path></svg>
                        <p class="sesion">Cerrar sesión</p>
                     </a>
                <?php endif; ?>
            </div>
            <button class="menu-toggle" id="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>

     <div class="layout-container">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-title">Módulos del Sistema</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="index.php?route=dashboard" class="<?php echo $isDashboardActive ? 'active' : ''; ?>">
                         <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M3 13H11V3H3V13ZM3 21H11V15H3V21ZM13 21H21V11H13V21ZM13 3V9H21V3H13Z"></path></svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?route=servicios_index" class="<?php echo $isServiciosActive ? 'active' : ''; ?>">
                        <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M14.1213 10.4792C13.7308 10.0886 13.0976 10.0886 12.7071 10.4792L12 11.1863C11.2189 11.9673 9.95259 11.9673 9.17154 11.1863C8.39049 10.4052 8.39049 9.13888 9.17154 8.35783L14.8022 2.72568C16.9061 2.24973 19.2008 2.83075 20.8388 4.46875C23.2582 6.88811 23.3716 10.7402 21.1792 13.2939L19.071 15.4289L14.1213 10.4792ZM3.16113 4.46875C5.33452 2.29536 8.66411 1.98283 11.17 3.53116L7.75732 6.94362C6.19523 8.50572 6.19523 11.0384 7.75732 12.6005C9.27209 14.1152 11.6995 14.1611 13.2695 12.7382L13.4142 12.6005L17.6568 16.8431L13.4142 21.0858C12.6331 21.8668 11.3668 21.8668 10.5858 21.0858L3.16113 13.6611C0.622722 11.1227 0.622722 7.00715 3.16113 4.46875Z"></path></svg>
                         <span>Servicios</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?route=socios_index" class="<?php echo $isSociosActive ? 'active' : ''; ?>">
                        <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.2914 5.99994H20.0002C20.5525 5.99994 21.0002 6.44766 21.0002 6.99994V13.9999C21.0002 14.5522 20.5525 14.9999 20.0002 14.9999H18.0002L13.8319 9.16427C13.3345 8.46797 12.4493 8.16522 11.6297 8.41109L9.14444 9.15668C8.43971 9.3681 7.6758 9.17551 7.15553 8.65524L6.86277 8.36247C6.41655 7.91626 6.49011 7.17336 7.01517 6.82332L12.4162 3.22262C13.0752 2.78333 13.9312 2.77422 14.5994 3.1994L18.7546 5.8436C18.915 5.94571 19.1013 5.99994 19.2914 5.99994ZM5.02708 14.2947L3.41132 15.7085C2.93991 16.1209 2.95945 16.8603 3.45201 17.2474L8.59277 21.2865C9.07284 21.6637 9.77592 21.5264 10.0788 20.9963L10.7827 19.7645C11.2127 19.012 11.1091 18.0682 10.5261 17.4269L7.82397 14.4545C7.09091 13.6481 5.84722 13.5771 5.02708 14.2947ZM7.04557 5H3C2.44772 5 2 5.44772 2 6V13.5158C2 13.9242 2.12475 14.3173 2.35019 14.6464C2.3741 14.6238 2.39856 14.6015 2.42357 14.5796L4.03933 13.1658C5.47457 11.91 7.65103 12.0343 8.93388 13.4455L11.6361 16.4179C12.6563 17.5401 12.8376 19.1918 12.0851 20.5087L11.4308 21.6538C11.9937 21.8671 12.635 21.819 13.169 21.4986L17.5782 18.8531C18.0786 18.5528 18.2166 17.8896 17.8776 17.4146L12.6109 10.0361C12.4865 9.86205 12.2652 9.78636 12.0603 9.84783L9.57505 10.5934C8.34176 10.9634 7.00492 10.6264 6.09446 9.7159L5.80169 9.42313C4.68615 8.30759 4.87005 6.45035 6.18271 5.57524L7.04557 5Z"></path></svg>
                        <span>Socios</span>
                     </a>
                </li>
                <li>
                    <a href="index.php?route=ejemplares_index" class="<?php echo $isEjemplaresActive ? 'active' : ''; ?>">
                       <svg class="menu-icon" viewBox="-2.5 0 63 63" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>Horse-shoe</title> <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Horse-shoe" transform="translate(1.000000, 1.000000)" stroke="#000000" stroke-width="2"> <path d="M52,54 L48.3,54 C53.1,45.3 56,35.5 56,28 C56,12.5 43.5,0 28,0 C12.5,0 0,12.5 0,28 C0,35.5 4,45.3 8.8,54 L5,54 L5,61 L17.9,61 C23.9,61 21.5,56.8 21.5,56.8 C21.5,56.8 9.8,38.5 9.8,27.8 C9.8,17.9 18,9.9 28.1,9.9 C38.2,9.9 46.4,17.9 46.4,27.8 C46.4,38.3 39.5,48.3 36.3,56.5 C35.2,59.2 36.4,61 40.1,61 L52,61 L52,54 L52,54 Z"></path> <path d="M27,6 L29,6"></path> <path d="M12,10 L14,10"></path> <path d="M41,10 L43,10"></path> <path d="M48,18 L50,18"></path> <path d="M6,17.9 L8,17.9"></path> <path d="M50,26 L52,26"></path> <path d="M50,35 L52,35"></path> <path d="M5,35 L7,35"></path> <path d="M8,44 L10,44"></path> <path d="M47,44 L49,44"></path> <path d="M43,54 L44.9,54"></path> <path d="M12,54 L14,54"></path> <path d="M4,26 L6,26"></path> </g> </g> </g></svg>
                         <span>Ejemplares</span>
                    </a>
                </li>
                <li>
                     <a href="index.php?route=medicos_index" class="<?php echo $isMedicosActive ? 'active' : ''; ?>">
                        <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M9 3H15V5H9V3ZM19 5H17V3C17 1.89543 16.1046 1 15 1H9C7.89543 1 7 1.89543 7 3V5H5C3.89543 5 3 5.89543 3 7V21C3 22.1046 3.89543 23 5 23H19C20.1046 23 21 22.1046 21 21V7C21 5.89543 20.1046 5 19 5ZM11 15H8V13H11V10H13V13H16V15H13V18H11V15Z"></path></svg>
                        <span>Médicos</span>
                     </a>
                </li>
                <li>
                    <a href="index.php?route=empleados_index" class="<?php echo $isEmpleadosActive ? 'active' : ''; ?>">
                        <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20 22H4C3.44772 22 3 21.5523 3 21V3C3 2.44772 3.44772 2 4 2H20C20.5523 2 21 2.44772 21 3V21C21 21.5523 20.5523 22 20 22ZM19 4H5V20H19V4ZM12 13C10.6193 13 9.5 11.8807 9.5 10.5C9.5 9.11929 10.6193 8 12 8C13.3807 8 14.5 9.11929 14.5 10.5C14.5 11.8807 13.3807 13 12 13ZM7.5 18C7.5 15.5147 9.51472 13.5 12 13.5C14.4853 13.5 16.5 15.5147 16.5 18H7.5Z"></path></svg>
                         <span>Empleados</span>
                    </a>
                </li>
                <?php if(is_admin()): ?>
                <li class="has-submenu <?php echo $isAdminSectionActive ? 'open' : ''; ?>">
                    <a href="#">
                        <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12.4142 5H21C21.5523 5 22 5.44772 22 6V20C22 20.5523 21.5523 21 21 21H3C2.44772 21 2 20.5523 2 20V4C2 3.44772 2.44772 3 3 3H10.4142L12.4142 5ZM13.2893 11.2071L15.4107 13.3284L11.1674 17.5717L11.1656 17.5735L8.33719 17.5717L8.33899 14.7433L12.5823 10.5L13.2893 11.2071Z"></path></svg>
                         <span>Administración</span>
                    </a>
                    <ul class="sidebar-submenu <?php echo $isAdminSectionActive ? 'visible' : ''; ?>">
                        <li>
                            <a href="index.php?route=usuarios_index" class="<?php echo (strpos($currentRoute, 'usuarios') !== false) ? 'active' : ''; ?>">
                                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 14C14.2091 14 16 12.2091 16 10C16 7.79086 14.2091 6 12 6C9.79086 6 8 7.79086 8 10C8 12.2091 9.79086 14 12 14ZM12 16C7.58172 16 4 17.7909 4 20V21H20V20C20 17.7909 16.4183 16 12 16Z"></path></svg>
                                 <span>Usuarios</span>
                            </a>
                        </li>
                        <li>
                             <a href="index.php?route=tipos_servicios_index" class="<?php echo (strpos($currentRoute, 'tipos_servicios') !== false) ? 'active' : ''; ?>">
                                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M6 3H18C19.1046 3 20 3.89543 20 5V21C20 22.1046 19.1046 23 18 23H6C4.89543 23 4 22.1046 4 21V5C4 3.89543 4.89543 3 6 3ZM18 5H6V21H18V5ZM8 7H16V9H8V7ZM8 11H16V13H8V11Z"></path></svg>
                                <span>Tipos de Servicio</span>
                            </a>
                        </li>
                        <li>
                             <a href="index.php?route=reportes" class="<?php echo ($currentRoute === 'reportes') ? 'active' : ''; ?>">
                                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20 22H4C3.44772 22 3 21.5523 3 21V3C3 2.44772 3.44772 2 4 2H16L21 7V21C21 21.5523 20.5523 22 20 22ZM19 8H15V4H5V20H19V8ZM7 10H17V12H7V10ZM7 14H17V16H7V14Z"></path></svg>
                                <span>Reportes</span>
                            </a>
                        </li>
                    </ul>
                </li>
                 <?php endif; ?>
            </ul>
        </div>

        <div class="content">
            <?php
            if (isset($_SESSION['message'])) {
                echo "<script>
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: '" . htmlspecialchars($_SESSION['message']) . "',
                        showConfirmButton: false,
                        timer: 2000
                    });
                </script>";
                unset($_SESSION['message']);
            }
            
            if (isset($_SESSION['error'])) {
                echo "<div class='alert alert-error'>" . htmlspecialchars($_SESSION['error']) . "</div>";
                unset($_SESSION['error']);
             }

            if (isset($_SESSION['warning'])) {
                echo "<div class='alert alert-warning'>" . htmlspecialchars($_SESSION['warning']) . "</div>";
                unset($_SESSION['warning']);
            }
            
            if (isset($contentView) && file_exists($contentView)) {
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

                // --- INICIO DE NUEVO CÓDIGO ---
                if (isset($resultados)) extract(['resultados' => $resultados]);
                if (isset($filtros_aplicados)) extract(['filtros_aplicados' => $filtros_aplicados]);
                // --- FIN DE NUEVO CÓDIGO ---

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
                    parentLi.classList.toggle('open');
                    const submenu = parentLi.querySelector('.sidebar-submenu');
                    submenu.classList.toggle('visible');
                 });
            });

            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.getElementById('sidebar');
            if(menuToggle && sidebar) {
                menuToggle.addEventListener('click', function() {
                     sidebar.classList.toggle('is-open');
                     this.classList.toggle('is-active');
                });
            }
        });
    </script>
</body>
</html>