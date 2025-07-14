<!-- app/views/servicios/edit.php -->

<?php
// Asegurar que las variables existan
$servicio = isset($servicio) && is_array($servicio) ? $servicio : null;
$tiposServicioList = $tiposServicioList ?? [];
$sociosList = $sociosList ?? [];
$ejemplares = $ejemplares ?? [];
$medicosList = $medicosList ?? [];
$posiblesEstados = $posiblesEstados ?? [];
$documentosServicio = $documentosServicio ?? [];
?>
<h2>Detalle / Edición Servicio #<?php echo htmlspecialchars($servicio['id_servicio'] ?? 'N/A'); ?></h2>

<?php
// Mostrar mensajes de sesión
if (isset($_SESSION['error'])) { echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); }
if(isset($_SESSION['message'])){ echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>"; unset($_SESSION['message']); }
if (isset($_SESSION['warning'])) { echo "<div class='alert alert-warning'>" . $_SESSION['warning'] . "</div>"; unset($_SESSION['warning']); }
?>

<div class="form-container">
    <?php if ($servicio): ?>
    <form action="index.php?route=servicios_update" method="POST" id="servicioEditForm" enctype="multipart/form-data">
        <input type="hidden" name="id_servicio" value="<?php echo htmlspecialchars($servicio['id_servicio']); ?>">
        <input type="hidden" name="socio_id" value="<?php echo htmlspecialchars($servicio['socio_id']); ?>">
        <input type="hidden" name="tipo_servicio_id" value="<?php echo htmlspecialchars($servicio['tipo_servicio_id']); ?>">
        <input type="hidden" name="ejemplar_id" value="<?php echo htmlspecialchars($servicio['ejemplar_id'] ?? ''); ?>">
        
        <fieldset>
            <legend>Información General</legend>
            <div class="form-group">
                <label>Tipo de Servicio:</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($servicio['tipo_servicio_nombre'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($servicio['codigo_servicio'] ?: 'S/C'); ?>)" disabled>
            </div>
            <div class="form-group">
                <label>Socio Solicitante:</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars(($servicio['socio_apPaterno'] ?? '') . ' ' . ($servicio['socio_apMaterno'] ?? '') . ', ' . ($servicio['socio_nombre'] ?? '')); ?> (<?php echo htmlspecialchars($servicio['socio_codigo_ganadero'] ?? 'S/C'); ?>)" disabled>
            </div>
            <div class="form-group">
                <label>Ejemplar:</label>
                 <input type="text" class="form-control" value="<?php echo !empty($servicio['ejemplar_id']) ? htmlspecialchars(($servicio['ejemplar_nombre'] ?? 'ID:'.$servicio['ejemplar_id']) . ' (' . ($servicio['ejemplar_codigo'] ?? 'S/C') . ')') : 'N/A'; ?>" disabled>
            </div>
             <div class="form-group">
                <label for="fechaSolicitud">Fecha Solicitud:</label>
                <input type="date" class="form-control" name="fechaSolicitud" id="fechaSolicitud" value="<?php echo htmlspecialchars($servicio['fechaSolicitud'] ?? ''); ?>" readonly>
            </div>
        </fieldset>
        
        <fieldset>
            <legend>Estado y Seguimiento</legend>
             <div class="form-group">
                 <label for="estado">Estado Actual: <span class="text-danger">*</span></label>
                 <select class="form-control" name="estado" id="estado" required>
                      <?php foreach($posiblesEstados as $est): ?>
                         <option value="<?php echo $est; ?>" <?php echo (isset($servicio['estado']) && $servicio['estado'] === $est) ? 'selected' : ''; ?>>
                             <?php echo htmlspecialchars($est); ?>
                         </option>
                     <?php endforeach; ?>
                 </select>
            </div>
             <div class="form-group">
                <label for="motivo_rechazo">Motivo del Rechazo: <span class="text-danger">*</span></label>
                <textarea class="form-control <?php echo (isset($servicio['estado']) && $servicio['estado'] !== 'Rechazado') ? 'hidden' : ''; ?>" name="motivo_rechazo" id="motivo_rechazo" rows="3" <?php echo (isset($servicio['estado']) && $servicio['estado'] === 'Rechazado') ? 'required' : ''; ?>><?php echo htmlspecialchars($servicio['motivo_rechazo'] ?? ''); ?></textarea>
            </div>
        </fieldset>
        
        <fieldset>
             <legend>Documentos Asociados a este Servicio</legend>
             <div>
                 <h4>Documentos Actuales del Servicio:</h4>
                 <?php if (!empty($documentosServicio)): ?>
                     <ul class="document-list">
                         <?php foreach ($documentosServicio as $doc): ?>
                              <li class="document-list-item">
                                  <div class="document-list-item-content">
                                     <strong><?php echo htmlspecialchars($doc['tipoDocumento'] ?? 'Desconocido'); ?>:</strong>
                                     <a href="index.php?route=documento_download&id=<?php echo $doc['id_documento']; ?>" target="_blank"> <?php echo htmlspecialchars($doc['nombreArchivoOriginal'] ?? 'Archivo'); ?> </a>
                                     <small>(Subido: <?php echo isset($doc['fechaSubida']) ? date('d/m/Y H:i', strtotime($doc['fechaSubida'])) : 'N/A'; ?> por <?php echo htmlspecialchars($doc['uploaded_by_username'] ?? 'N/A'); ?>)</small>
                                     <?php if(!empty($doc['comentarios'])): ?> <p class="document-comment"><i>Comentarios: <?php echo htmlspecialchars($doc['comentarios']); ?></i></p> <?php endif; ?>
                                  </div>
                                  <div class="document-list-item-actions">
                                      <?php if (isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'superusuario'): ?>
                                         <a href="index.php?route=documento_delete&id=<?php echo $doc['id_documento']; ?>&servicio_id=<?php echo $servicio['id_servicio']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar documento?')">Eliminar</a>
                                      <?php endif; ?>
                                  </div>
                            </li>
                         <?php endforeach; ?>
                     </ul>
                 <?php else: ?> <p>No hay docs asociados a este servicio.</p> <?php endif; ?>
             </div>
             <hr>
             <h4>Subir/Actualizar Documentos del Servicio:</h4>
              <div class="form-group">
                 <label for="solicitud_file_edit">Solicitud de Servicio:</label>
                 <input type="file" class="form-control" name="solicitud_file_edit" id="solicitud_file_edit" accept=".pdf,.jpg,.jpeg,.png,.gif">
              </div>
             <div class="form-group">
                 <label for="pago_file_edit">Comprobante de Pago:</label>
                 <input type="file" class="form-control" name="pago_file_edit" id="pago_file_edit" accept=".pdf,.jpg,.jpeg,.png,.gif">
             </div>
        </fieldset>
        
        <fieldset>
             <legend>Auditoría</legend>
             <p><small>Registrado por: <?php echo htmlspecialchars($servicio['registrador_username'] ?? 'N/A'); ?> el <?php echo isset($servicio['fecha_registro']) ? date('d/m/Y H:i', strtotime($servicio['fecha_registro'])) : 'N/A'; ?></small></p>
             <p><small>Última modificación por: <?php echo htmlspecialchars($servicio['modificador_username'] ?? 'N/A'); ?> el <?php echo isset($servicio['fecha_modificacion']) ? date('d/m/Y H:i', strtotime($servicio['fecha_modificacion'])) : 'N/A'; ?></small></p>
        </fieldset>
        
        <div>
            <button type="submit" class="btn btn-primary">Guardar Cambios Servicio</button>
            <a href="index.php?route=servicios_index" class="btn btn-secondary">Volver al Listado</a>
             <?php if (isset($servicio['estado']) && !in_array($servicio['estado'], ['Cancelado', 'Completado', 'Rechazado'])): ?>
                 <a href="index.php?route=servicios_cancel&id=<?php echo $servicio['id_servicio']; ?>" class="btn btn-danger float-right" onclick="return confirm('¿Estás seguro de CANCELAR este servicio?')">Cancelar Servicio</a>
             <?php endif; ?>
        </div>

    </form>
     <?php else: ?>
        <div class='alert alert-error'>Servicio no encontrado.</div>
        <a href="index.php?route=servicios_index" class="btn btn-secondary">Volver al listado</a>
    <?php endif; ?>
</div>

<script>
 document.addEventListener('DOMContentLoaded', function() {
     const estadoSelect = document.getElementById('estado');
     const motivoRechazoTextarea = document.getElementById('motivo_rechazo');

     function toggleMotivoRechazo() {
         if (!estadoSelect || !motivoRechazoTextarea) return;
         
         if (estadoSelect.value === 'Rechazado') {
             motivoRechazoTextarea.parentElement.classList.remove('hidden'); // Muestra el div padre
             motivoRechazoTextarea.required = true;
         } else {
             motivoRechazoTextarea.parentElement.classList.add('hidden'); // Oculta el div padre
             motivoRechazoTextarea.required = false;
         }
     }

     if(estadoSelect) {
         estadoSelect.addEventListener('change', toggleMotivoRechazo);
         toggleMotivoRechazo(); // Ejecutar al cargar para establecer el estado inicial
     }

     // Poner fecha máxima hoy a los campos de fecha editables
     const today = new Date().toISOString().split('T')[0];
     const dateInputs = ['fechaRecepcionDocs', 'fechaPago', 'fechaAsignacionMedico', 'fechaVisitaMedico', 'fechaEnvioLG', 'fechaRecepcionLG', 'fechaFinalizacion'];
     dateInputs.forEach(id => {
          const input = document.getElementById(id);
          if(input) {
              input.setAttribute('max', today);
          }
     });
 });
</script>