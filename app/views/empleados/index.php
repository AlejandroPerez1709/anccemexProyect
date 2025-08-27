<?php
// app/views/empleados/index.php

// Helper para construir la URL con los parámetros de búsqueda y página
function build_pagination_url($page, $searchTerm) {
    $query_params = ['route' => 'empleados_index', 'page' => $page];
    if (!empty($searchTerm)) {
        $query_params['search'] = $searchTerm;
    }
    return 'index.php?' . http_build_query($query_params);
}
?>

<div class="page-title-container">
    <h2>Listado de Empleados</h2>
</div>

<div class="table-header-controls">
    <a href="index.php?route=empleados/create" class="btn btn-primary">Registrar Nuevo Empleado</a>
    <a href="index.php?route=empleados_export_excel&search=<?php echo urlencode($searchTerm ?? ''); ?>" class="btn btn-secondary">Exportar a Excel</a>
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="route" value="empleados_index">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, puesto, email..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <div class="search-buttons">
            <button type="submit" class="btn btn-secondary">Buscar</button>
             <a href="index.php?route=empleados_index" class="btn btn-primary">Limpiar</a>
        </div>
    </form>
</div>

<?php if (isset($total_pages) && $total_pages > 1): ?>
<nav class="pagination-container">
    <ul class="pagination">
        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page - 1, $searchTerm); ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo build_pagination_url($i, $searchTerm); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page + 1, $searchTerm); ?>">Siguiente</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php if(isset($_SESSION['message'])){ echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>"; unset($_SESSION['message']); } ?>
<?php if(isset($_SESSION['error'])){ echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); } ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Nombre</th>
                 <th>Apellidos</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Puesto</th>
                <th>Estado</th>
                <th>Fecha Ingreso</th>
                 <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($empleados) && count($empleados) > 0): ?>
                <?php foreach($empleados as $empleado): ?>
                    <tr class="clickable-row"
                         data-nombre-completo="<?php echo htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido_paterno'] . ' ' . $empleado['apellido_materno']); ?>"
                        data-email="<?php echo htmlspecialchars($empleado['email'] ?? '-'); ?>"
                        data-telefono="<?php echo htmlspecialchars($empleado['telefono'] ?? '-'); ?>"
                        data-direccion="<?php echo htmlspecialchars($empleado['direccion'] ?? '-'); ?>"
                        data-puesto="<?php echo htmlspecialchars($empleado['puesto'] ?? '-'); ?>"
                        data-fecha-ingreso="<?php echo !empty($empleado['fecha_ingreso']) ? date('d/m/Y', strtotime($empleado['fecha_ingreso'])) : '-'; ?>"
                        data-estado="<?php echo htmlspecialchars(ucfirst($empleado['estado'])); ?>">
                        <td><?php echo $empleado['id_empleado']; ?></td>
                        <td><?php echo htmlspecialchars($empleado['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($empleado['apellido_paterno'] . ' ' . $empleado['apellido_materno']); ?></td>
                         <td><?php echo htmlspecialchars($empleado['email']); ?></td>
                        <td><?php echo htmlspecialchars($empleado['telefono']); ?></td>
                        <td><?php echo htmlspecialchars($empleado['puesto']); ?></td>
                        <td>
                             <span style="color: <?php echo ($empleado['estado'] == 'activo') ? 'green' : 'red'; ?>; font-weight: bold;">
                                <?php echo htmlspecialchars(ucfirst($empleado['estado'])); ?>
                            </span>
                        </td>
                        <td><?php echo isset($empleado['fecha_ingreso']) ? date('d/m/Y', strtotime($empleado['fecha_ingreso'])) : ''; ?></td>
                        <td>
                            <div class="action-buttons">
                                 <a href="index.php?route=empleados/edit&id=<?php echo $empleado['id_empleado']; ?>" class="btn btn-warning">Editar</a>
                                <button class="btn btn-danger" onclick="confirmDeactivation(event, <?php echo $empleado['id_empleado']; ?>, '<?php echo htmlspecialchars(addslashes($empleado['nombre'] . ' ' . $empleado['apellido_paterno'])); ?>')">
                                    Desactivar
                                 </button>
                            </div>
                        </td>
                    </tr>
                 <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">
                        <?php if (!empty($searchTerm)): ?>
                             No se encontraron empleados que coincidan con "<?php echo htmlspecialchars($searchTerm); ?>".
                        <?php else: ?>
                            No hay empleados registrados.
                        <?php endif; ?>
                     </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (isset($total_pages) && $total_pages > 1): ?>
<nav class="pagination-container">
    <ul class="pagination">
        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page - 1, $searchTerm); ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo build_pagination_url($i, $searchTerm); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page + 1, $searchTerm); ?>">Siguiente</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<div id="infoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close-button">&times;</span>
            <h2 id="modalTitle">Detalles del Empleado</h2>
        </div>
        <div class="modal-body">
            <div class="modal-section">
                 <div class="modal-section-title">
                    <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20 22H4C3.44772 22 3 21.5523 3 21V3C3 2.44772 3.44772 2 4 2H20C20.5523 2 21 2.44772 21 3V21C21 21.5523 20.5523 22 20 22ZM19 4H5V20H19V4ZM12 13C10.6193 13 9.5 11.8807 9.5 10.5C9.5 9.11929 10.6193 8 12 8C13.3807 8 14.5 9.11929 14.5 10.5C14.5 11.8807 13.3807 13 12 13ZM7.5 18C7.5 15.5147 9.51472 13.5 12 13.5C14.4853 13.5 16.5 15.5147 16.5 18H7.5Z"></path></svg>
                     <h4>Información del Empleado</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field full-width"><span class="modal-label">Nombre Completo:</span><span class="modal-value" id="modalNombreCompleto"></span></div>
                    <div class="modal-field"><span class="modal-label">Puesto:</span><span class="modal-value" id="modalPuesto"></span></div>
                    <div class="modal-field"><span class="modal-label">Email:</span><span class="modal-value" id="modalEmail"></span></div>
                    <div class="modal-field"><span class="modal-label">Teléfono:</span><span class="modal-value" id="modalTelefono"></span></div>
                    <div class="modal-field full-width"><span class="modal-label">Dirección:</span><span class="modal-value" id="modalDireccion"></span></div>
                </div>
             </div>
            <div class="modal-section">
                <div class="modal-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7C4.79086 3 3 4.79086 3 7V17C3 19.2091 4.79086 21 7 21H17C19.2091 21 21 19.2091 21 17V7C21 4.79086 19.2091 3 17 3ZM19 17C19 18.1046 18.1046 19 17 19H7C5.89543 19 5 18.1046 5 17V7C5 5.89543 5.89543 5 7 5H17C18.1046 5 19 5.89543 19 7V17ZM15.2929 9.29289L11 13.5858L8.70711 11.2929L7.29289 12.7071L11 16.4142L16.7071 10.7071L15.2929 9.29289Z"></path></svg>
                    <h4>Estado y Antigüedad</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field"><span class="modal-label">Fecha de Ingreso:</span><span class="modal-value" id="modalFechaIngreso"></span></div>
                     <div class="modal-field"><span class="modal-label">Estado:</span><span class="modal-value" id="modalEstado"></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>/assets/js/empleados-index.js"></script>