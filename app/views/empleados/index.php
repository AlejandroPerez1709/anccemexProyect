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

<h2>Listado de Empleados</h2>

<div class="table-header-controls">
    <a href="index.php?route=empleados/create" class="btn btn-primary">Registrar Nuevo Empleado</a>
    <a href="index.php?route=empleados_export_excel&search=<?php echo urlencode($searchTerm ?? ''); ?>" class="btn btn-secondary">Exportar a Excel</a>
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="route" value="empleados_index">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, puesto, email..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <button type="submit" class="btn btn-secondary">Buscar</button>
        <a href="index.php?route=empleados_index" class="btn btn-primary">Limpiar</a>
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
            <p><strong>Nombre Completo:</strong> <span id="modalNombreCompleto"></span></p>
            <p><strong>Puesto:</strong> <span id="modalPuesto"></span></p>
            <p><strong>Email:</strong> <span id="modalEmail"></span></p>
            <p><strong>Teléfono:</strong> <span id="modalTelefono"></span></p>
            <p><strong>Dirección:</strong> <span id="modalDireccion"></span></p>
            <hr>
            <p><strong>Fecha de Ingreso:</strong> <span id="modalFechaIngreso"></span></p>
            <p><strong>Estado:</strong> <span id="modalEstado"></span></p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lógica para el modal de información
    const modal = document.getElementById('infoModal');
    const closeButton = modal.querySelector('.close-button');
    const rows = document.querySelectorAll('.clickable-row');

    // Referencias a los spans del modal
    const modalNombreCompleto = document.getElementById('modalNombreCompleto');
    const modalPuesto = document.getElementById('modalPuesto');
    const modalEmail = document.getElementById('modalEmail');
    const modalTelefono = document.getElementById('modalTelefono');
    const modalDireccion = document.getElementById('modalDireccion');
    const modalFechaIngreso = document.getElementById('modalFechaIngreso');
    const modalEstado = document.getElementById('modalEstado');

    rows.forEach(row => {
        row.addEventListener('click', function(event) {
            if (event.target.closest('.action-buttons')) {
                return;
            }

            // Llenar datos generales del modal
            modalNombreCompleto.textContent = this.dataset.nombreCompleto;
            modalPuesto.textContent = this.dataset.puesto;
            modalEmail.textContent = this.dataset.email;
            modalTelefono.textContent = this.dataset.telefono;
            modalDireccion.textContent = this.dataset.direccion;
            modalFechaIngreso.textContent = this.dataset.fechaIngreso;
            modalEstado.textContent = this.dataset.estado;
            
            modal.style.display = 'block';
        });
    });

    closeButton.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
});

function confirmDeactivation(event, empleadoId, empleadoName) {
    // Detener la propagación para que no active el modal de la fila
    event.stopPropagation();

    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se desactivará al empleado: ${empleadoName}`,
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Razón de la desactivación',
        inputPlaceholder: 'Escribe el motivo aquí...',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return '¡Necesitas escribir una razón para la desactivación!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `index.php?route=empleados_delete&id=${empleadoId}`;
            
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'razon';
            reasonInput.value = result.value;
            form.appendChild(reasonInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>