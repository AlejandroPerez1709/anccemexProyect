<?php 
//app/views/tipos_servicios/index.php
?>

<div class="page-title-container">
    <h2>Catálogo: Tipos de Servicio</h2>
</div>

<div class="table-header-controls">
    <a href="index.php?route=tipos_servicios/create" class="btn btn-primary">Registrar Nuevo Tipo</a>
    <a href="index.php?route=tipos_servicios_export_excel" class="btn btn-secondary">Exportar a Excel</a>
</div>

<?php if(isset($_SESSION['error'])){ echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); } ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Nombre</th>
                <th>Código</th>
                <th>Requiere Médico</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $tiposServicios = $tiposServicios ?? [];
            if(count($tiposServicios) > 0):
            ?>
                <?php foreach($tiposServicios as $tipo): ?>
                    <tr class="clickable-row" 
                        data-nombre="<?php echo htmlspecialchars($tipo['nombre']); ?>"
                        data-codigo="<?php echo htmlspecialchars($tipo['codigo_servicio'] ?? '-'); ?>"
                        data-descripcion="<?php echo htmlspecialchars($tipo['descripcion'] ?? 'No hay descripción disponible.'); ?>"
                        data-req-medico="<?php echo !empty($tipo['requiere_medico']) ? 'Sí' : 'No'; ?>"
                        data-estado="<?php echo htmlspecialchars(ucfirst($tipo['estado'])); ?>">
                        
                        <td><?php echo $tipo['id_tipo_servicio']; ?></td>
                        <td><?php echo htmlspecialchars($tipo['nombre']); ?></td>
                        <td><?php echo !empty($tipo['codigo_servicio']) ? htmlspecialchars($tipo['codigo_servicio']) : '-'; ?></td>
                        <td><?php echo !empty($tipo['requiere_medico']) ? 'Sí' : 'No'; ?></td>
                        <td>
                            <span style="color: <?php echo ($tipo['estado'] == 'activo') ? 'green' : 'red'; ?>; font-weight: bold;">
                                <?php echo htmlspecialchars(ucfirst($tipo['estado'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="index.php?route=tipos_servicios/edit&id=<?php echo $tipo['id_tipo_servicio']; ?>" class="btn btn-warning">Editar</a>
                                <a href="index.php?route=tipos_servicios_delete&id=<?php echo $tipo['id_tipo_servicio']; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este tipo de servicio?')">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No hay tipos de servicio registrados</td> 
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Estructura HTML de la Ventana Modal para Tipos de Servicio -->
<div id="infoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close-button">&times;</span>
            <h2 id="modalTitle">Detalles del Tipo de Servicio</h2>
        </div>
        <div class="modal-body">
            <div class="modal-section">
                <div class="modal-section-title">
                     <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M21 8V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V4C3 3.44772 3.44772 3 4 3H12V8H21ZM19 10H12V5H5V19H19V10ZM14 10V12H16V10H14ZM14 13V15H16V13H14ZM10 13V15H7V13H10Z"></path></svg>
                    <h4>Información del Servicio</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field full-width"><span class="modal-label">Nombre del Servicio:</span><span class="modal-value" id="modalNombre"></span></div>
                    <div class="modal-field"><span class="modal-label">Código Oficial:</span><span class="modal-value" id="modalCodigo"></span></div>
                    <div class="modal-field"><span class="modal-label">Requiere Médico:</span><span class="modal-value" id="modalReqMedico"></span></div>
                    <div class="modal-field"><span class="modal-label">Estado:</span><span class="modal-value" id="modalEstado"></span></div>
                    <div class="modal-field full-width"><span class="modal-label">Descripción:</span><span class="modal-value" id="modalDescripcion"></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a los elementos del DOM
    const modal = document.getElementById('infoModal');
    const closeButton = document.querySelector('.close-button');
    const rows = document.querySelectorAll('.clickable-row');

    // Contenido del modal
    const modalNombre = document.getElementById('modalNombre');
    const modalCodigo = document.getElementById('modalCodigo');
    const modalEstado = document.getElementById('modalEstado');
    const modalReqMedico = document.getElementById('modalReqMedico');
    const modalDescripcion = document.getElementById('modalDescripcion');

    // Abrir el modal al hacer clic en una fila
    rows.forEach(row => {
        row.addEventListener('click', function(event) {
            // Evita abrir el modal si se hizo clic en un botón dentro de la fila
            if (event.target.closest('.action-buttons')) {
                return;
            }

            // Llenar el modal con los datos de la fila
            modalNombre.textContent = this.dataset.nombre;
            modalCodigo.textContent = this.dataset.codigo;
            modalEstado.textContent = this.dataset.estado;
            modalReqMedico.textContent = this.dataset.reqMedico;
            modalDescripcion.textContent = this.dataset.descripcion;

            // Mostrar el modal
            modal.style.display = 'block';
        });
    });

    // Cerrar el modal con el botón 'X'
    closeButton.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Cerrar el modal si se hace clic fuera de la ventana
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
});
</script>
