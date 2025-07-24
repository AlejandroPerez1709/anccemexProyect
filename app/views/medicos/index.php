<?php
//app/views/medicos/index.php

// Helper para construir la URL con los parámetros de búsqueda y página
function build_pagination_url($page, $searchTerm) {
    $query_params = ['route' => 'medicos_index', 'page' => $page];
    if (!empty($searchTerm)) {
        $query_params['search'] = $searchTerm;
    }
    return 'index.php?' . http_build_query($query_params);
}
?>

<div class="page-title-container">
    <h2>Listado de Médicos</h2>
</div>

<div class="table-header-controls">
    <a href="index.php?route=medicos/create" class="btn btn-primary">Registrar Nuevo Médico</a>
    <a href="index.php?route=medicos_export_excel&search=<?php echo urlencode($searchTerm ?? ''); ?>" class="btn btn-secondary">Exportar a Excel</a>
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="route" value="medicos_index">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, cédula, email..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <button type="submit" class="btn btn-secondary">Buscar</button>
        <a href="index.php?route=medicos_index" class="btn btn-primary">Limpiar</a>
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
                <th>Cédula Prof.</th>
                <th>Cert. ANCCE</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($medicos) && count($medicos) > 0): ?>
                <?php foreach($medicos as $medico): ?>
                    <tr class="clickable-row"
                        data-nombre-completo="<?php echo htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido_paterno'] . ' ' . $medico['apellido_materno']); ?>"
                        data-especialidad="<?php echo htmlspecialchars($medico['especialidad'] ?? '-'); ?>"
                        data-email="<?php echo htmlspecialchars($medico['email'] ?? '-'); ?>"
                        data-telefono="<?php echo htmlspecialchars($medico['telefono'] ?? '-'); ?>"
                        data-cedula="<?php echo htmlspecialchars($medico['numero_cedula_profesional'] ?? '-'); ?>"
                        data-residencia="<?php echo htmlspecialchars($medico['entidad_residencia'] ?? '-'); ?>"
                        data-certificacion="<?php echo htmlspecialchars($medico['numero_certificacion_ancce'] ?? '-'); ?>"
                        data-estado="<?php echo htmlspecialchars(ucfirst($medico['estado'])); ?>">
                        <td><?php echo $medico['id_medico']; ?></td>
                        <td><?php echo htmlspecialchars($medico['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($medico['apellido_paterno'] . ' ' . $medico['apellido_materno']); ?></td>
                        <td><?php echo !empty($medico['email']) ? htmlspecialchars($medico['email']) : '-'; ?></td>
                        <td><?php echo !empty($medico['telefono']) ? htmlspecialchars($medico['telefono']) : '-'; ?></td>
                        <td><?php echo !empty($medico['numero_cedula_profesional']) ? htmlspecialchars($medico['numero_cedula_profesional']) : '-'; ?></td>
                        <td><?php echo !empty($medico['numero_certificacion_ancce']) ? htmlspecialchars($medico['numero_certificacion_ancce']) : '-'; ?></td>
                        <td>
                            <span style="color: <?php echo ($medico['estado'] == 'activo') ? 'green' : 'red'; ?>; font-weight: bold;">
                                <?php echo htmlspecialchars(ucfirst($medico['estado'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="index.php?route=medicos/edit&id=<?php echo $medico['id_medico']; ?>" class="btn btn-warning">Editar</a>
                                <button class="btn btn-danger" onclick="confirmDeactivation(event, <?php echo $medico['id_medico']; ?>, '<?php echo htmlspecialchars(addslashes($medico['nombre'] . ' ' . $medico['apellido_paterno'])); ?>')">
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
                            No se encontraron médicos que coincidan con "<?php echo htmlspecialchars($searchTerm); ?>".
                        <?php else: ?>
                            No hay médicos registrados.
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

<!-- Estructura HTML de la Ventana Modal para Médicos -->
<div id="infoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close-button">&times;</span>
            <h2 id="modalTitle">Detalles del Médico</h2>
        </div>
        <div class="modal-body">
            <div class="modal-section">
                <div class="modal-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 14C14.2091 14 16 12.2091 16 10C16 7.79086 14.2091 6 12 6C9.79086 6 8 7.79086 8 10C8 12.2091 9.79086 14 12 14ZM12 16C7.58172 16 4 17.7909 4 20V21H20V20C20 17.7909 16.4183 16 12 16Z"></path></svg>
                    <h4>Información Personal</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field full-width"><span class="modal-label">Nombre Completo:</span><span class="modal-value" id="modalNombreCompleto"></span></div>
                    <div class="modal-field"><span class="modal-label">Email:</span><span class="modal-value" id="modalEmail"></span></div>
                    <div class="modal-field"><span class="modal-label">Teléfono:</span><span class="modal-value" id="modalTelefono"></span></div>
                </div>
            </div>
            <div class="modal-section">
                <div class="modal-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7C4.79086 3 3 4.79086 3 7V17C3 19.2091 4.79086 21 7 21H17C19.2091 21 21 19.2091 21 17V7C21 4.79086 19.2091 3 17 3ZM19 17C19 18.1046 18.1046 19 17 19H7C5.89543 19 5 18.1046 5 17V7C5 5.89543 5.89543 5 7 5H17C18.1046 5 19 5.89543 19 7V17ZM15.2929 9.29289L11 13.5858L8.70711 11.2929L7.29289 12.7071L11 16.4142L16.7071 10.7071L15.2929 9.29289Z"></path></svg>
                    <h4>Datos Profesionales</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field"><span class="modal-label">Especialidad:</span><span class="modal-value" id="modalEspecialidad"></span></div>
                    <div class="modal-field"><span class="modal-label">Cédula Profesional:</span><span class="modal-value" id="modalCedula"></span></div>
                    <div class="modal-field"><span class="modal-label">Certificación ANCCE:</span><span class="modal-value" id="modalCertificacion"></span></div>
                    <div class="modal-field"><span class="modal-label">Entidad de Residencia:</span><span class="modal-value" id="modalResidencia"></span></div>
                    <div class="modal-field"><span class="modal-label">Estado:</span><span class="modal-value" id="modalEstado"></span></div>
                </div>
            </div>
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
    const modalEspecialidad = document.getElementById('modalEspecialidad');
    const modalEmail = document.getElementById('modalEmail');
    const modalTelefono = document.getElementById('modalTelefono');
    const modalCedula = document.getElementById('modalCedula');
    const modalCertificacion = document.getElementById('modalCertificacion');
    const modalResidencia = document.getElementById('modalResidencia');
    const modalEstado = document.getElementById('modalEstado');

    rows.forEach(row => {
        row.addEventListener('click', function(event) {
            if (event.target.closest('.action-buttons')) {
                return;
            }

            // Llenar datos generales del modal
            modalNombreCompleto.textContent = this.dataset.nombreCompleto;
            modalEspecialidad.textContent = this.dataset.especialidad;
            modalEmail.textContent = this.dataset.email;
            modalTelefono.textContent = this.dataset.telefono;
            modalCedula.textContent = this.dataset.cedula;
            modalCertificacion.textContent = this.dataset.certificacion;
            modalResidencia.textContent = this.dataset.residencia;
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

function confirmDeactivation(event, medicoId, medicoName) {
    // Detener la propagación para que no active el modal de la fila
    event.stopPropagation();
    
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se desactivará al médico: ${medicoName}`,
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
            form.action = `index.php?route=medicos_delete&id=${medicoId}`;

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