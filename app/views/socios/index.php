<?php
//app/views/socios/index.php

// Helper para construir la URL con los parámetros de búsqueda y página
function build_pagination_url($page, $searchTerm) {
    $query_params = ['route' => 'socios_index', 'page' => $page];
    if (!empty($searchTerm)) {
        $query_params['search'] = $searchTerm;
    }
    return 'index.php?' . http_build_query($query_params);
}
?>

<div class="page-title-container">
    <h2>Listado de Socios</h2>
</div>

<div class="table-header-controls">
    <a href="index.php?route=socios/create" class="btn btn-primary">Registrar Nuevo Socio</a>
    <a href="index.php?route=socios_export_excel&search=<?php echo urlencode($searchTerm ?? ''); ?>" class="btn btn-secondary">Exportar a Excel</a>
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="route" value="socios_index">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, ganadería, código..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <button type="submit" class="btn btn-secondary">Buscar</button>
        <a href="index.php?route=socios_index" class="btn btn-primary">Limpiar</a>
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
                <th>Nombre Titular</th>
                <th>Apellidos</th>
                <th>Nombre Ganadería</th>
                <th>Email Contacto</th>
                <th>Teléfono Contacto</th>
                <th>Cód. Ganadero</th>
                <th>Estado</th>
                <th>Fecha Reg.</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($socios) && count($socios) > 0): ?>
                <?php foreach($socios as $socio): ?>
                     <tr class="clickable-row"
                         data-nombre-completo="<?php echo htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido_paterno'] . ' ' . $socio['apellido_materno']); ?>"
                         data-rfc="<?php echo htmlspecialchars($socio['identificacion_fiscal_titular'] ?? '-'); ?>"
                         data-email="<?php echo htmlspecialchars($socio['email'] ?? '-'); ?>"
                         data-telefono="<?php echo htmlspecialchars($socio['telefono'] ?? '-'); ?>"
                         data-ganaderia="<?php echo htmlspecialchars($socio['nombre_ganaderia'] ?? '-'); ?>"
                         data-codigo="<?php echo htmlspecialchars($socio['codigoGanadero'] ?? '-'); ?>"
                         data-direccion="<?php echo htmlspecialchars($socio['direccion'] ?? '-'); ?>"
                         data-fecha-registro="<?php echo !empty($socio['fechaRegistro']) ? date('d/m/Y', strtotime($socio['fechaRegistro'])) : '-'; ?>"
                         data-estado="<?php echo htmlspecialchars(ucfirst($socio['estado'])); ?>"
                         data-doc-id="<?php echo $socio['document_status']['ID_OFICIAL_TITULAR'] ? '1' : '0'; ?>"
                         data-doc-rfc="<?php echo $socio['document_status']['CONSTANCIA_FISCAL'] ? '1' : '0'; ?>"
                         data-doc-domicilio="<?php echo $socio['document_status']['COMPROBANTE_DOM_GANADERIA'] ? '1' : '0'; ?>"
                         data-doc-propiedad="<?php echo $socio['document_status']['TITULO_PROPIEDAD_RANCHO'] ? '1' : '0'; ?>">
                        <td><?php echo $socio['id_socio']; ?></td>
                        <td><?php echo htmlspecialchars($socio['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($socio['apellido_paterno'] . ' ' . $socio['apellido_materno']); ?></td>
                        <td><?php echo !empty($socio['nombre_ganaderia']) ? htmlspecialchars($socio['nombre_ganaderia']) : '-'; ?></td>
                        <td><?php echo !empty($socio['email']) ? htmlspecialchars($socio['email']) : '-'; ?></td>
                        <td><?php echo !empty($socio['telefono']) ? htmlspecialchars($socio['telefono']) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($socio['codigoGanadero']); ?></td>
                        <td><span style="color: <?php echo ($socio['estado'] == 'activo') ? 'green' : 'red'; ?>; font-weight: bold;"><?php echo htmlspecialchars(ucfirst($socio['estado'])); ?></span></td>
                        <td><?php echo isset($socio['fechaRegistro']) ? date('d/m/Y', strtotime($socio['fechaRegistro'])) : '-'; ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="index.php?route=socios/edit&id=<?php echo $socio['id_socio']; ?>" class="btn btn-warning">Editar</a>
                                <button class="btn btn-danger" onclick="confirmDeactivation(<?php echo $socio['id_socio']; ?>, '<?php echo htmlspecialchars(addslashes($socio['nombre'] . ' ' . $socio['apellido_paterno'])); ?>')">
                                    Desactivar
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center">
                        <?php if (!empty($searchTerm)): ?>
                            No se encontraron socios que coincidan con "<?php echo htmlspecialchars($searchTerm); ?>".
                        <?php else: ?>
                            No hay socios registrados.
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

<!-- INICIO DE MODIFICACIÓN: Nueva estructura del Modal -->
<div id="infoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close-button">&times;</span>
            <h2 id="modalTitle">Detalles del Socio</h2>
        </div>
        <div class="modal-body">
            <div class="modal-section">
                <div class="modal-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 14C14.2091 14 16 12.2091 16 10C16 7.79086 14.2091 6 12 6C9.79086 6 8 7.79086 8 10C8 12.2091 9.79086 14 12 14ZM12 16C7.58172 16 4 17.7909 4 20V21H20V20C20 17.7909 16.4183 16 12 16Z"></path></svg>
                    <h4>Información del Titular</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field"><span class="modal-label">Nombre Completo:</span><span class="modal-value" id="modalNombreCompleto"></span></div>
                    <div class="modal-field"><span class="modal-label">RFC:</span><span class="modal-value" id="modalRfc"></span></div>
                    <div class="modal-field"><span class="modal-label">Email:</span><span class="modal-value" id="modalEmail"></span></div>
                    <div class="modal-field"><span class="modal-label">Teléfono:</span><span class="modal-value" id="modalTelefono"></span></div>
                </div>
            </div>
            <div class="modal-section">
                <div class="modal-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M21 21H3V11L12 3L21 11V21ZM19 19V12L12 5.69L5 12V19H19Z"></path></svg>
                    <h4>Datos de la Ganadería</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field"><span class="modal-label">Nombre Ganadería:</span><span class="modal-value" id="modalGanaderia"></span></div>
                    <div class="modal-field"><span class="modal-label">Código Ganadero:</span><span class="modal-value" id="modalCodigo"></span></div>
                    <div class="modal-field full-width"><span class="modal-label">Dirección:</span><span class="modal-value" id="modalDireccion"></span></div>
                </div>
            </div>
            <div class="modal-section">
                <div class="modal-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7C4.79086 3 3 4.79086 3 7V17C3 19.2091 4.79086 21 7 21H17C19.2091 21 21 19.2091 21 17V7C21 4.79086 19.2091 3 17 3ZM19 17C19 18.1046 18.1046 19 17 19H7C5.89543 19 5 18.1046 5 17V7C5 5.89543 5.89543 5 7 5H17C18.1046 5 19 5.89543 19 7V17ZM15.2929 9.29289L11 13.5858L8.70711 11.2929L7.29289 12.7071L11 16.4142L16.7071 10.7071L15.2929 9.29289Z"></path></svg>
                    <h4>Estado y Documentos</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field"><span class="modal-label">Fecha de Registro:</span><span class="modal-value" id="modalFechaRegistro"></span></div>
                    <div class="modal-field"><span class="modal-label">Estado:</span><span class="modal-value" id="modalEstado"></span></div>
                </div>
                <div class="modal-docs">
                    <label class="custom-checkbox-container">Identificación Oficial<input type="checkbox" id="modalDocId" disabled><span class="checkmark"></span></label>
                    <label class="custom-checkbox-container">Constancia Fiscal (RFC)<input type="checkbox" id="modalDocRfc" disabled><span class="checkmark"></span></label>
                    <label class="custom-checkbox-container">Comprobante de Domicilio<input type="checkbox" id="modalDocDomicilio" disabled><span class="checkmark"></span></label>
                    <label class="custom-checkbox-container">Título de Propiedad<input type="checkbox" id="modalDocPropiedad" disabled><span class="checkmark"></span></label>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- FIN DE MODIFICACIÓN -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lógica para el modal de información
    const modal = document.getElementById('infoModal');
    const closeButton = modal.querySelector('.close-button');
    const rows = document.querySelectorAll('.clickable-row');

    // Referencias a los spans del modal
    const modalNombreCompleto = document.getElementById('modalNombreCompleto');
    const modalRfc = document.getElementById('modalRfc');
    const modalEmail = document.getElementById('modalEmail');
    const modalTelefono = document.getElementById('modalTelefono');
    const modalGanaderia = document.getElementById('modalGanaderia');
    const modalCodigo = document.getElementById('modalCodigo');
    const modalDireccion = document.getElementById('modalDireccion');
    const modalFechaRegistro = document.getElementById('modalFechaRegistro');
    const modalEstado = document.getElementById('modalEstado');
    // Checkboxes de documentos
    const modalDocId = document.getElementById('modalDocId');
    const modalDocRfc = document.getElementById('modalDocRfc');
    const modalDocDomicilio = document.getElementById('modalDocDomicilio');
    const modalDocPropiedad = document.getElementById('modalDocPropiedad');

    rows.forEach(row => {
        row.addEventListener('click', function(event) {
            if (event.target.closest('.action-buttons')) {
                return;
            }

            // Llenar datos generales
            modalNombreCompleto.textContent = this.dataset.nombreCompleto;
            modalRfc.textContent = this.dataset.rfc;
            modalEmail.textContent = this.dataset.email;
            modalTelefono.textContent = this.dataset.telefono;
            modalGanaderia.textContent = this.dataset.ganaderia;
            modalCodigo.textContent = this.dataset.codigo;
            modalDireccion.textContent = this.dataset.direccion;
            modalFechaRegistro.textContent = this.dataset.fechaRegistro;
            modalEstado.textContent = this.dataset.estado;

            // Marcar/desmarcar checkboxes
            modalDocId.checked = this.dataset.docId === '1';
            modalDocRfc.checked = this.dataset.docRfc === '1';
            modalDocDomicilio.checked = this.dataset.docDomicilio === '1';
            modalDocPropiedad.checked = this.dataset.docPropiedad === '1';
            
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

function confirmDeactivation(socioId, socioName) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se desactivará al socio: ${socioName}`,
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
            form.action = `index.php?route=socios_delete&id=${socioId}`;
            
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