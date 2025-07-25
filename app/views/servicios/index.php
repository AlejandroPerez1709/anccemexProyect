<?php
//app/views/servicios/index.php

// Helper para construir la URL con los parámetros de filtros y página
function build_pagination_url($page, $filters) {
    $query_params = ['route' => 'servicios_index', 'page' => $page];
    
    if (!empty($filters['estado'])) {
        $query_params['filtro_estado'] = $filters['estado'];
    }
    if (!empty($filters['socio_id'])) {
        $query_params['filtro_socio_id'] = $filters['socio_id'];
    }
    if (!empty($filters['tipo_servicio_id'])) {
        $query_params['filtro_tipo_id'] = $filters['tipo_servicio_id'];
    }
    
    return 'index.php?' . http_build_query($query_params);
}

// Preparamos los filtros actuales para el enlace de exportación
$export_filters = [];
if (!empty($_GET['filtro_estado'])) $export_filters['filtro_estado'] = $_GET['filtro_estado'];
if (!empty($_GET['filtro_socio_id'])) $export_filters['filtro_socio_id'] = $_GET['filtro_socio_id'];
if (!empty($_GET['filtro_tipo_id'])) $export_filters['filtro_tipo_id'] = $_GET['filtro_tipo_id'];
?>

<div class="page-title-container">
    <h2>Listado de Servicios Solicitados</h2>
</div>

<div class="table-header-controls">
    <a href="index.php?route=servicios/create" class="btn btn-primary">Registrar Nuevo Servicio</a>
    <a href="index.php?route=servicios_export_excel&<?php echo http_build_query($export_filters); ?>" class="btn btn-secondary">Exportar a Excel</a>
</div>

<form action="index.php" method="GET" class="filter-form">
     <input type="hidden" name="route" value="servicios_index">
     <div class="filter-controls">
         <div class="filter-item">
             <label for="filtro_estado" class="filter-label">Estado:</label>
             <select name="filtro_estado" id="filtro_estado" class="form-control">
                  <option value="">-- Todos --</option>
                  <?php
                   $estadosPosiblesFiltro = ['Pendiente Docs/Pago', 'Recibido Completo', 'Pendiente Visita Medico', 'Pendiente Resultado Lab', 'Enviado a LG', 'Pendiente Respuesta LG', 'Completado', 'Rechazado', 'Cancelado'];
                   $estadoSeleccionado = $_GET['filtro_estado'] ?? '';
                   foreach ($estadosPosiblesFiltro as $est) {
                       echo "<option value=\"$est\"" . ($estadoSeleccionado === $est ? ' selected' : '') . ">$est</option>";
                   }
                 ?>
             </select>
         </div>
         <div class="filter-item">
              <label for="filtro_socio_id" class="filter-label">Socio:</label>
              <select name="filtro_socio_id" id="filtro_socio_id" class="form-control">
                  <option value="">-- Todos --</option>
                  <?php
                   $socioSeleccionado = $_GET['filtro_socio_id'] ?? '';
                   foreach($sociosList as $id => $display) {
                       echo "<option value=\"$id\"" . ($socioSeleccionado == $id ? ' selected' : '') . ">" . htmlspecialchars($display) . "</option>";
                   }
                  ?>
              </select>
         </div>
          <div class="filter-item">
                <label for="filtro_tipo_id" class="filter-label">Tipo Servicio:</label>
                <select name="filtro_tipo_id" id="filtro_tipo_id" class="form-control">
                    <option value="">-- Todos --</option>
                      <?php
                       $tipoSeleccionado = $_GET['filtro_tipo_id'] ?? '';
                       foreach($tiposServicioList as $id => $display) {
                           echo "<option value=\"$id\"" . ($tipoSeleccionado == $id ? ' selected' : '') . ">" . htmlspecialchars($display) . "</option>";
                       }
                       ?>
                </select>
          </div>
          <div class="filter-buttons">
               <button type="submit" class="btn btn-secondary btn-sm">Filtrar</button>
               <a href="index.php?route=servicios_index" class="btn btn-primary btn-sm">Limpiar</a>
          </div>
        </div>
</form>

<?php if (isset($total_pages) && $total_pages > 1): ?>
<nav class="pagination-container">
    <ul class="pagination">
        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page - 1, $filters); ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo build_pagination_url($i, $filters); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page + 1, $filters); ?>">Siguiente</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php if(isset($_SESSION['error'])){ echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); } ?>
<?php if(isset($_SESSION['warning'])){ echo "<div class='alert alert-warning'>" . $_SESSION['warning'] . "</div>"; unset($_SESSION['warning']); } ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Tipo Servicio (Código)</th>
                <th>Socio (Cód. Gan.)</th>
                <th>Ejemplar</th>
                <th>Estado</th>
                <th>Fecha Solicitud</th>
                <th>Últ. Modif.</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($servicios) && count($servicios) > 0): ?>
                <?php foreach($servicios as $servicio): ?>
                    <tr class="clickable-row"
                        data-id-servicio="<?php echo $servicio['id_servicio']; ?>"
                        data-tipo-servicio="<?php echo htmlspecialchars($servicio['tipo_servicio_nombre'] . ' (' . ($servicio['codigo_servicio'] ?: 'N/A') . ')'); ?>"
                        data-socio="<?php echo htmlspecialchars($servicio['socio_nombre'] . ' ' . $servicio['socio_apPaterno'] . ' (' . ($servicio['socio_codigo_ganadero'] ?? 'S/C') . ')'); ?>"
                        data-ejemplar="<?php echo htmlspecialchars($servicio['ejemplar_nombre'] ?? 'N/A'); ?>"
                        data-estado="<?php echo htmlspecialchars($servicio['estado']); ?>"
                        data-fecha-solicitud="<?php echo !empty($servicio['fechaSolicitud']) ? date('d/m/Y', strtotime($servicio['fechaSolicitud'])) : '-'; ?>"
                        data-ultima-modif="<?php echo !empty($servicio['fecha_modificacion']) ? date('d/m/Y H:i', strtotime($servicio['fecha_modificacion'])) . ' por ' . htmlspecialchars($servicio['modificador_username'] ?? 'Sistema') : '-'; ?>"
                        data-doc-solicitud="<?php echo $servicio['document_status']['SOLICITUD_SERVICIO'] ? '1' : '0'; ?>"
                        data-doc-pago="<?php echo $servicio['document_status']['COMPROBANTE_PAGO'] ? '1' : '0'; ?>">
                        <td><?php echo $servicio['id_servicio']; ?></td>
                        <td><?php echo htmlspecialchars($servicio['tipo_servicio_nombre'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($servicio['codigo_servicio'] ?: 'N/A'); ?>)</td>
                        <td><?php echo htmlspecialchars($servicio['socio_apPaterno'] . ', ' . $servicio['socio_nombre']); ?> (<?php echo htmlspecialchars($servicio['socio_codigo_ganadero'] ?? 'S/C'); ?>)</td>
                        <td><?php echo htmlspecialchars($servicio['ejemplar_nombre'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower(str_replace(['/', ' '], ['-', '-'], $servicio['estado'])); ?>">
                                 <?php echo htmlspecialchars($servicio['estado']); ?>
                            </span>
                        </td>
                        <td><?php echo isset($servicio['fechaSolicitud']) ? date('d/m/Y', strtotime($servicio['fechaSolicitud'])) : '-'; ?></td>
                        <td><?php echo isset($servicio['fecha_modificacion']) ? date('d/m/Y H:i', strtotime($servicio['fecha_modificacion'])) : '-'; ?> por <?php echo htmlspecialchars($servicio['modificador_username'] ?? 'Sistema'); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="index.php?route=servicios/edit&id=<?php echo $servicio['id_servicio']; ?>" class="btn btn-warning btn-sm">Ver/Editar</a>
                                <?php if (!in_array($servicio['estado'], ['Cancelado', 'Completado', 'Rechazado'])): ?>
                                    <a href="index.php?route=servicios_cancel&id=<?php echo $servicio['id_servicio']; ?>" class="btn btn-danger btn-sm" onclick="confirmCancel(event, this.href, <?php echo $servicio['id_servicio']; ?>)">Cancelar</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No hay servicios que coincidan con los filtros aplicados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (isset($total_pages) && $total_pages > 1): ?>
<nav class="pagination-container">
    <ul class="pagination">
        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page - 1, $filters); ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo build_pagination_url($i, $filters); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page + 1, $filters); ?>">Siguiente</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Estructura HTML de la Ventana Modal para Servicios -->
<div id="infoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close-button">&times;</span>
            <h2 id="modalTitle">Detalles del Servicio</h2>
        </div>
        <div class="modal-body">
            <div class="modal-section">
                <div class="modal-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M21 8V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V4C3 3.44772 3.44772 3 4 3H12V8H21ZM19 10H12V5H5V19H19V10ZM14 10V12H16V10H14ZM14 13V15H16V13H14ZM10 13V15H7V13H10Z"></path></svg>
                    <h4>Información del Trámite</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field"><span class="modal-label">Número de Servicio:</span><span class="modal-value" id="modalIdServicio"></span></div>
                    <div class="modal-field"><span class="modal-label">Tipo de Servicio:</span><span class="modal-value" id="modalTipoServicio"></span></div>
                    <div class="modal-field"><span class="modal-label">Socio:</span><span class="modal-value" id="modalSocio"></span></div>
                    <div class="modal-field"><span class="modal-label">Ejemplar:</span><span class="modal-value" id="modalEjemplar"></span></div>
                </div>
            </div>
            <div class="modal-section">
                <div class="modal-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2ZM12 4C7.58172 4 4 7.58172 4 12C4 16.4183 7.58172 20 12 20C16.4183 20 20 16.4183 20 12C20 7.58172 16.4183 4 12 4ZM12.5 8V12.5L16 14.25L15.25 15.4L11 13V8H12.5Z"></path></svg>
                    <h4>Seguimiento</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field"><span class="modal-label">Estado Actual:</span><span class="modal-value" id="modalEstado"></span></div>
                    <div class="modal-field"><span class="modal-label">Fecha de Solicitud:</span><span class="modal-value" id="modalFechaSolicitud"></span></div>
                    <div class="modal-field full-width"><span class="modal-label">Última Modificación:</span><span class="modal-value" id="modalUltimaModif"></span></div>
                </div>
                <div class="modal-docs">
                    <label class="custom-checkbox-container">Solicitud de Servicio<input type="checkbox" id="modalDocSolicitud" disabled><span class="checkmark"></span></label>
                    <label class="custom-checkbox-container">Comprobante de Pago<input type="checkbox" id="modalDocPago" disabled><span class="checkmark"></span></label>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lógica para el modal de información
    const modal = document.getElementById('infoModal');
    if(modal) {
        const closeButton = modal.querySelector('.close-button');
        const rows = document.querySelectorAll('.clickable-row');

        // Referencias a los spans del modal
        const modalIdServicio = document.getElementById('modalIdServicio');
        const modalTipoServicio = document.getElementById('modalTipoServicio');
        const modalSocio = document.getElementById('modalSocio');
        const modalEjemplar = document.getElementById('modalEjemplar');
        const modalEstado = document.getElementById('modalEstado');
        const modalFechaSolicitud = document.getElementById('modalFechaSolicitud');
        const modalUltimaModif = document.getElementById('modalUltimaModif');
        const modalDocSolicitud = document.getElementById('modalDocSolicitud');
        const modalDocPago = document.getElementById('modalDocPago');

        rows.forEach(row => {
            row.addEventListener('click', function(event) {
                if (event.target.closest('.action-buttons')) {
                    return;
                }

                // Llenar datos del modal
                modalIdServicio.textContent = this.dataset.idServicio;
                modalTipoServicio.textContent = this.dataset.tipoServicio;
                modalSocio.textContent = this.dataset.socio;
                modalEjemplar.textContent = this.dataset.ejemplar;
                modalEstado.textContent = this.dataset.estado;
                modalFechaSolicitud.textContent = this.dataset.fechaSolicitud;
                modalUltimaModif.textContent = this.dataset.ultimaModif;
                modalDocSolicitud.checked = this.dataset.docSolicitud === '1';
                modalDocPago.checked = this.dataset.docPago === '1';
                
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
    }
});

// Script para la confirmación de cancelación de servicio
function confirmCancel(event, url, servicioId) {
    event.preventDefault(); // Previene que el enlace se siga inmediatamente
    event.stopPropagation(); // Previene que se abra el modal
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se cancelará el servicio #${servicioId}. ¡Esta acción no se puede deshacer!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar servicio',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            // Si el usuario confirma, ahora sí se redirige al enlace
            window.location.href = url;
        }
    });
}
</script>