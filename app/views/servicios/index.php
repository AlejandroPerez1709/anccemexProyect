<?php
//app/views/servicios/index.php

function build_pagination_url($page) {
    $query_params = $_GET; 
    $query_params['page'] = $page;
    if (!isset($query_params['route'])) {
        $query_params['route'] = 'servicios_index';
    }
    return 'index.php?' . http_build_query($query_params);
}

$export_filters = [];
if (isset($_GET['filtro_estado'])) $export_filters['filtro_estado'] = $_GET['filtro_estado'];
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
                  <?php
                   // --- INICIO DE MODIFICACIÓN: Añadir opción "En Proceso" ---
                   $estadoSeleccionado = $_GET['filtro_estado'] ?? 'en_proceso'; // Por defecto, 'en_proceso'
                   $estadosPosiblesFiltro = [
                       'en_proceso' => 'En Proceso',
                       '' => '-- Todos --',
                       'Pendiente Docs/Pago' => 'Pendiente Docs/Pago',
                       'Recibido Completo' => 'Recibido Completo',
                       'Pendiente Visita Medico' => 'Pendiente Visita Medico',
                       'Pendiente Resultado Lab' => 'Pendiente Resultado Lab',
                       'Enviado a LG' => 'Enviado a LG',
                       'Pendiente Respuesta LG' => 'Pendiente Respuesta LG',
                       'Completado' => 'Completado',
                       'Rechazado' => 'Rechazado',
                       'Cancelado' => 'Cancelado'
                   ];

                   foreach ($estadosPosiblesFiltro as $valor => $texto) {
                       echo "<option value=\"$valor\"" . ($estadoSeleccionado === $valor ? ' selected' : '') . ">$texto</option>";
                   }
                   // --- FIN DE MODIFICACIÓN ---
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
            <a class="page-link" href="<?php echo build_pagination_url($page - 1); ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
             <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo build_pagination_url($i); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page + 1); ?>">Siguiente</a>
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
                     <?php
                        $rowClass = 'clickable-row';
                        if ($servicio['health_status'] === 'advertencia') {
                            $rowClass .= ' fila-advertencia';
                        } elseif ($servicio['health_status'] === 'retrasado') {
                            $rowClass .= ' fila-retrasado';
                        }
                     ?>
                     <tr class="<?php echo $rowClass; ?>"
                        data-id-servicio="<?php echo $servicio['id_servicio']; ?>"
                        data-tipo-servicio="<?php echo htmlspecialchars($servicio['tipo_servicio_nombre'] . ' (' . ($servicio['codigo_servicio'] ?: 'N/A') . ')'); ?>"
                        data-socio="<?php echo htmlspecialchars($servicio['socio_nombre'] . ' ' . $servicio['socio_apPaterno'] . ' (' . ($servicio['socio_codigo_ganadero'] ?? 'S/C') . ')'); ?>"
                        data-ejemplar="<?php echo htmlspecialchars(($servicio['ejemplar_nombre'] ?? 'N/A') . ' (' . ($servicio['ejemplar_codigo'] ?? 'S/C') . ')'); ?>"
                         data-observaciones="<?php echo htmlspecialchars($servicio['descripcion'] ?? ''); ?>"
                        data-medico-asignado="<?php echo !empty($servicio['medico_nombre']) ? htmlspecialchars($servicio['medico_nombre'] . ' ' . $servicio['medico_apPaterno']) : ''; ?>"
                        data-estado="<?php echo htmlspecialchars($servicio['estado']); ?>"
                        
                        data-fecha-solicitud="<?php echo !empty($servicio['fechaSolicitud']) ? date('d/m/Y', strtotime($servicio['fechaSolicitud'])) : ''; ?>"
                        data-fecha-asignacion-medico="<?php echo !empty($servicio['fechaAsignacionMedico']) ? date('d/m/Y', strtotime($servicio['fechaAsignacionMedico'])) : ''; ?>"
                        data-fecha-visita-medico="<?php echo !empty($servicio['fechaVisitaMedico']) ? date('d/m/Y', strtotime($servicio['fechaVisitaMedico'])) : ''; ?>"
                        data-fecha-envio-lg="<?php echo !empty($servicio['fechaEnvioLG']) ? date('d/m/Y', strtotime($servicio['fechaEnvioLG'])) : ''; ?>"
                        data-fecha-recepcion-lg="<?php echo !empty($servicio['fechaRecepcionLG']) ? date('d/m/Y', strtotime($servicio['fechaRecepcionLG'])) : ''; ?>"
                        data-fecha-finalizacion="<?php echo !empty($servicio['fechaFinalizacion']) ? date('d/m/Y', strtotime($servicio['fechaFinalizacion'])) : ''; ?>"
                        data-ultima-modif="<?php echo !empty($servicio['fecha_modificacion']) ? date('d/m/Y H:i', strtotime($servicio['fecha_modificacion'])) . ' por ' . htmlspecialchars($servicio['modificador_username'] ?? 'Sistema') : '-'; ?>"
                        data-doc-solicitud-id="<?php echo $servicio['document_status']['SOLICITUD_SERVICIO'] ?: '0'; ?>"
                        data-doc-pago-id="<?php echo $servicio['document_status']['COMPROBANTE_PAGO'] ?: '0'; ?>">
                        
                        <td><?php echo $servicio['id_servicio']; ?></td>
                        <td><?php echo htmlspecialchars($servicio['tipo_servicio_nombre'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($servicio['codigo_servicio'] ?: 'N/A'); ?>)</td>
                        <td><?php echo htmlspecialchars($servicio['socio_apPaterno'] . ', ' . $servicio['socio_nombre']); ?> (<?php echo htmlspecialchars($servicio['socio_codigo_ganadero'] ?? 'S/C'); ?>)</td>
                        <td><?php echo htmlspecialchars($servicio['ejemplar_nombre'] ?? 'N/A'); ?></td>
                        <td>
                            <span id="status-badge-<?php echo $servicio['id_servicio']; ?>" class="status-badge status-<?php echo strtolower(str_replace(['/', ' '], ['-', '-'], $servicio['estado'])); ?>">
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
            <a class="page-link" href="<?php echo build_pagination_url($page - 1); ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
             <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo build_pagination_url($i); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page + 1); ?>">Siguiente</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<div id="infoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close-button">&times;</span>
            <h2 id="modalTitle">Detalles del Servicio</h2>
        </div>
        <div class="modal-body">
            <div class="modal-section">
                 <div class="modal-section-title">
                    <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M14.1213 10.4792C13.7308 10.0886 13.0976 10.0886 12.7071 10.4792L12 11.1863C11.2189 11.9673 9.95259 11.9673 9.17154 11.1863C8.39049 10.4052 8.39049 9.13888 9.17154 8.35783L14.8022 2.72568C16.9061 2.24973 19.2008 2.83075 20.8388 4.46875C23.2582 6.88811 23.3716 10.7402 21.1792 13.2939L19.071 15.4289L14.1213 10.4792ZM3.16113 4.46875C5.33452 2.29536 8.66411 1.98283 11.17 3.53116L7.75732 6.94362C6.19523 8.50572 6.19523 11.0384 7.75732 12.6005C9.27209 14.1152 11.6995 14.1611 13.2695 12.7382L13.4142 12.6005L17.6568 16.8431L13.4142 21.0858C12.6331 21.8668 11.3668 21.8668 10.5858 21.0858L3.16113 13.6611C0.622722 11.1227 0.622722 7.00715 3.16113 4.46875Z"></path></svg>
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
                     <h4>Seguimiento y Documentos</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field full-width">
                        <label for="modalEstadoSelect" class="modal-label">Cambiar Estado:</label>
                         <select id="modalEstadoSelect" class="form-control">
                            <option>Cargando estados...</option>
                        </select>
                     </div>
                     <div class="modal-field full-width" id="modalMotivoRechazoContainer" style="display: none;">
                        <label for="modalMotivoRechazo" class="modal-label">Motivo del Rechazo (Obligatorio):</label>
                        <textarea id="modalMotivoRechazo" rows="3" class="form-control"></textarea>
                     </div>
                   
                    <div class="modal-field"><span class="modal-label">Fecha de Solicitud:</span><span class="modal-value" id="modalFechaSolicitud"></span></div>
                    <div class="modal-field full-width" id="modalMedicoContainer"><span class="modal-label">Médico Asignado:</span><span class="modal-value" id="modalMedicoAsignado"></span></div>
                    <div class="modal-field" id="modalFechaAsignacionMedicoContainer"><span class="modal-label">Fecha Asignación Médico:</span><span class="modal-value" id="modalFechaAsignacionMedico"></span></div>
                     <div class="modal-field" id="modalFechaVisitaMedicoContainer"><span class="modal-label">Fecha Visita Médico:</span><span class="modal-value" id="modalFechaVisitaMedico"></span></div>
                    <div class="modal-field" id="modalFechaEnvioLgContainer"><span class="modal-label">Fecha Envío a LG:</span><span class="modal-value" id="modalFechaEnvioLg"></span></div>
                    <div class="modal-field" id="modalFechaRecepcionLgContainer"><span class="modal-label">Fecha Recepción LG:</span><span class="modal-value" id="modalFechaRecepcionLg"></span></div>
                     <div class="modal-field" id="modalFechaFinalizacionContainer"><span class="modal-label">Fecha de Finalización:</span><span class="modal-value" id="modalFechaFinalizacion"></span></div>
                    <div class="modal-field full-width"><span class="modal-label">Última Modificación:</span><span class="modal-value" id="modalUltimaModif"></span></div>
                    <div class="modal-field full-width"><span class="modal-label">Observaciones:</span><span class="modal-value" id="modalObservaciones"></span></div>
                    </div>
                <div class="modal-docs">
                     <label class="custom-checkbox-container">Solicitud de Servicio
                        <input type="checkbox" id="modalDocSolicitud" disabled><span class="checkmark"></span>
                        <a href="#" target="_blank" class="view-doc-icon" id="modalDocSolicitudView" title="Ver Documento">👁️</a>
                     </label>
                     <label class="custom-checkbox-container">Comprobante de Pago
                        <input type="checkbox" id="modalDocPago" disabled><span class="checkmark"></span>
                        <a href="#" target="_blank" class="view-doc-icon" id="modalDocPagoView" title="Ver Documento">👁️</a>
                     </label>
                 </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-button">Cerrar</button>
            <button id="btnGuardarEstado" class="btn btn-primary">Guardar Estado</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('infoModal');
    if(modal) {
        const closeButtons = modal.querySelectorAll('.close-button');
        const rows = document.querySelectorAll('.clickable-row');
        const modalIdServicio = document.getElementById('modalIdServicio');
        const modalTipoServicio = document.getElementById('modalTipoServicio');
        const modalSocio = document.getElementById('modalSocio');
        const modalEjemplar = document.getElementById('modalEjemplar');
        const modalObservaciones = document.getElementById('modalObservaciones');
        const modalMedicoAsignado = document.getElementById('modalMedicoAsignado');
        const modalUltimaModif = document.getElementById('modalUltimaModif');
        const modalDocSolicitud = document.getElementById('modalDocSolicitud');
        const modalDocPago = document.getElementById('modalDocPago');
        const modalDocSolicitudView = document.getElementById('modalDocSolicitudView');
        const modalDocPagoView = document.getElementById('modalDocPagoView');
        const modalEstadoSelect = document.getElementById('modalEstadoSelect');
        const btnGuardarEstado = document.getElementById('btnGuardarEstado');
        const modalMotivoRechazoContainer = document.getElementById('modalMotivoRechazoContainer');
        const modalMotivoRechazo = document.getElementById('modalMotivoRechazo');
        const modalFechaSolicitud = document.getElementById('modalFechaSolicitud');
        const modalFechaAsignacionMedico = document.getElementById('modalFechaAsignacionMedico');
        const modalFechaVisitaMedico = document.getElementById('modalFechaVisitaMedico');
        const modalFechaEnvioLg = document.getElementById('modalFechaEnvioLg');
        const modalFechaRecepcionLg = document.getElementById('modalFechaRecepcionLg');
        const modalFechaFinalizacion = document.getElementById('modalFechaFinalizacion');
        const modalMedicoContainer = document.getElementById('modalMedicoContainer');
        const modalFechaAsignacionMedicoContainer = document.getElementById('modalFechaAsignacionMedicoContainer');
        const modalFechaVisitaMedicoContainer = document.getElementById('modalFechaVisitaMedicoContainer');
        const modalFechaEnvioLgContainer = document.getElementById('modalFechaEnvioLgContainer');
        const modalFechaRecepcionLgContainer = document.getElementById('modalFechaRecepcionLgContainer');
        const modalFechaFinalizacionContainer = document.getElementById('modalFechaFinalizacionContainer');
        
        let currentServiceId = null;
        let currentRowElement = null;
        rows.forEach(row => {
            row.addEventListener('click', function(event) {
                if (event.target.closest('.action-buttons')) { return; }
                
                currentRowElement = this; 
                currentServiceId = this.dataset.idServicio;
                
                modalIdServicio.textContent = this.dataset.idServicio;
                modalTipoServicio.textContent = this.dataset.tipoServicio;
                modalSocio.textContent = this.dataset.socio;
                 modalEjemplar.textContent = this.dataset.ejemplar;
                modalObservaciones.textContent = this.dataset.observaciones || 'Sin observaciones.';
                modalUltimaModif.textContent = this.dataset.ultimaModif;

                function fillAndToggle(element, container, dataAttribute) {
                     if (dataAttribute && dataAttribute.trim() !== '') {
                        element.textContent = dataAttribute;
                         container.style.display = 'block';
                     } else {
                        container.style.display = 'none';
                     }
                }

                fillAndToggle(modalFechaSolicitud, modalFechaSolicitud.parentElement, this.dataset.fechaSolicitud);
                fillAndToggle(modalMedicoAsignado, modalMedicoContainer, this.dataset.medicoAsignado);
                fillAndToggle(modalFechaAsignacionMedico, modalFechaAsignacionMedicoContainer, this.dataset.fechaAsignacionMedico);
                fillAndToggle(modalFechaVisitaMedico, modalFechaVisitaMedicoContainer, this.dataset.fechaVisitaMedico);
                fillAndToggle(modalFechaEnvioLg, modalFechaEnvioLgContainer, this.dataset.fechaEnvioLg);
                fillAndToggle(modalFechaRecepcionLg, modalFechaRecepcionLgContainer, this.dataset.fechaRecepcionLg);
                fillAndToggle(modalFechaFinalizacion, modalFechaFinalizacionContainer, this.dataset.fechaFinalizacion);
                modalMotivoRechazo.value = '';
                let docSolicitudId = this.dataset.docSolicitudId;
                modalDocSolicitud.checked = docSolicitudId !== '0';
                if (docSolicitudId && docSolicitudId !== '0') {
                    modalDocSolicitudView.href = `index.php?route=documento_download&id=${docSolicitudId}`;
                    modalDocSolicitudView.style.display = 'inline-block';
                } else {
                    modalDocSolicitudView.style.display = 'none';
                }

                let docPagoId = this.dataset.docPagoId;
                modalDocPago.checked = docPagoId !== '0';
                if (docPagoId && docPagoId !== '0') {
                    modalDocPagoView.href = `index.php?route=documento_download&id=${docPagoId}`;
                    modalDocPagoView.style.display = 'inline-block';
                } else {
                    modalDocPagoView.style.display = 'none';
                }

                modalEstadoSelect.innerHTML = '<option>Cargando...</option>';
                modalEstadoSelect.disabled = true;
                
                fetch(`index.php?route=servicios_get_valid_states&id=${currentServiceId}`)
                    .then(response => response.json())
                    .then(estados => {
                        modalEstadoSelect.innerHTML = '';
                        estados.forEach(estado => {
                             const option = document.createElement('option');
                            option.value = estado;
                            option.textContent = estado;
                            modalEstadoSelect.appendChild(option);
                        });
                        
                        modalEstadoSelect.value = currentRowElement.dataset.estado;
                         modalEstadoSelect.disabled = false;
                        toggleMotivoRechazo();
                    })
                    .catch(error => {
                         console.error('Error al cargar los estados:', error);
                        modalEstadoSelect.innerHTML = '<option>Error al cargar</option>';
                    });
                
                modal.style.display = 'block';
            });
        });

        function closeModal() {
            modal.style.display = 'none';
        }

        closeButtons.forEach(btn => btn.addEventListener('click', closeModal));
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                closeModal();
            }
        });
        function toggleMotivoRechazo() {
            if (modalEstadoSelect.value === 'Rechazado') {
                modalMotivoRechazoContainer.style.display = 'block';
            } else {
                modalMotivoRechazoContainer.style.display = 'none';
            }
        }
        modalEstadoSelect.addEventListener('change', toggleMotivoRechazo);
        
        btnGuardarEstado.addEventListener('click', function() {
            const nuevoEstado = modalEstadoSelect.value;
            const motivo = modalMotivoRechazo.value;

            if (nuevoEstado === 'Rechazado' && motivo.trim() === '') {
                Swal.fire('Error', 'Debe proporcionar un motivo para el rechazo.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('id', currentServiceId);
            formData.append('estado', nuevoEstado);
            formData.append('motivo', motivo);

            fetch('index.php?route=servicios_update_status', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    closeModal();
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Estado actualizado correctamente',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Ocurrió un problema de comunicación con el servidor.', 'error');
            });
        });
    }
});
function confirmCancel(event, url, servicioId) {
    event.preventDefault(); 
    event.stopPropagation();
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
            window.location.href = url;
        }
    });
}
</script>