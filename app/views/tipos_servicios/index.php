<?php 
//app/views/tipos_servicios/index.php
?>
<h2>Catálogo: Tipos de Servicio</h2>

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

<div id="infoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close-button">&times;</span>
            <h2 id="modalTitle">Detalles del Tipo de Servicio</h2>
        </div>
        <div class="modal-body">
            <p><strong>Nombre:</strong> <span id="modalNombre"></span></p>
            <p><strong>Código:</strong> <span id="modalCodigo"></span></p>
            <p><strong>Estado:</strong> <span id="modalEstado"></span></p>
            <p><strong>Requiere Médico:</strong> <span id="modalReqMedico"></span></p>
            <p><strong>Descripción:</strong></p>
            <p id="modalDescripcion"></p>
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