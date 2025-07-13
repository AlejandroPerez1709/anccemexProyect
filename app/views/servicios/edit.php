<!-- app/views/servicios/edit.php -->

<?php
// Asegurar que las variables existan y sean del tipo esperado
$servicio = isset($servicio) && is_array($servicio) ? $servicio : null;
$tiposServicioList = $tiposServicioList ?? [];
$sociosList = $sociosList ?? [];
$ejemplares = $ejemplares ?? []; // Lista completa para filtrar select si se habilita
$medicosList = $medicosList ?? [];
$posiblesEstados = $posiblesEstados ?? []; // Debe venir del controlador
$documentosServicio = $documentosServicio ?? []; // Documentos asociados a ESTE servicio
?>
<h2>Detalle / Edición Servicio #<?php echo htmlspecialchars($servicio['id_servicio'] ?? 'N/A'); ?></h2>

<?php
// Mostrar mensajes de sesión
if (isset($_SESSION['error'])) { echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); }
if(isset($_SESSION['message'])){ echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>"; unset($_SESSION['message']); }
if (isset($_SESSION['warning'])) { echo "<div class='alert alert-warning'>" . $_SESSION['warning'] . "</div>"; unset($_SESSION['warning']); }
?>

<div class="form-container">
    <?php if ($servicio): // Solo mostrar formulario si $servicio tiene datos ?>
    <form action="index.php?route=servicios_update" method="POST" id="servicioEditForm" enctype="multipart/form-data">
        <input type="hidden" name="id_servicio" value="<?php echo htmlspecialchars($servicio['id_servicio']); ?>">
        <input type="hidden" name="socio_id" value="<?php echo htmlspecialchars($servicio['socio_id']); ?>">
        <input type="hidden" name="tipo_servicio_id" value="<?php echo htmlspecialchars($servicio['tipo_servicio_id']); ?>">
        <input type="hidden" name="ejemplar_id" value="<?php echo htmlspecialchars($servicio['ejemplar_id'] ?? ''); ?>"> <fieldset>
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
        </fieldset> <fieldset>
            <legend>Estado y Seguimiento</legend>
             <div class="form-group">
                 <label for="estado">Estado Actual: <span style="color: red;">*</span></label>
                 <select class="form-control" name="estado" id="estado" required>
                     <?php foreach($posiblesEstados as $est): ?>
                         <option value="<?php echo $est; ?>" <?php echo (isset($servicio['estado']) && $servicio['estado'] === $est) ? 'selected' : ''; ?>>
                             <?php echo htmlspecialchars($est); ?>
                         </option>
                     <?php endforeach; ?>
                 </select>
            </div>
             <div class="form-group">
                <label for="fechaRecepcionDocs">Fecha Recepción Docs Completos:</label>
                <input type="date" class="form-control" name="fechaRecepcionDocs" id="fechaRecepcionDocs" value="<?php echo htmlspecialchars($servicio['fechaRecepcionDocs'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="fechaPago">Fecha Registro Pago:</label>
                <input type="date" class="form-control" name="fechaPago" id="fechaPago" value="<?php echo htmlspecialchars($servicio['fechaPago'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="referencia_pago">Referencia de Pago:</label>
                <input type="text" class="form-control" name="referencia_pago" id="referencia_pago" value="<?php echo htmlspecialchars($servicio['referencia_pago'] ?? ''); ?>" maxlength="100">
            </div>

            <?php if(!empty($servicio['requiere_medico'])): ?>
                 <div class="form-group">
                    <label for="medico_id">Médico Asignado:</label>
                     <select class="form-control" name="medico_id" id="medico_id">
                         <option value="">-- Sin Asignar --</option>
                          <?php foreach ($medicosList as $id_med => $display_med): ?>
                            <option value="<?php echo $id_med; ?>" <?php echo (isset($servicio['medico_id']) && $servicio['medico_id'] == $id_med) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($display_med); ?>
                            </option>
                        <?php endforeach; ?>
                         <?php if (!empty($servicio['medico_id']) && !isset($medicosList[$servicio['medico_id']])): ?>
                               <option value="<?php echo htmlspecialchars($servicio['medico_id']); ?>" selected><?php echo htmlspecialchars(($servicio['medico_nombre'] ?? 'ID:'.$servicio['medico_id']) . ' ' . ($servicio['medico_apPaterno'] ?? '')) ?> [Actual, Inactivo?]</option>
                          <?php endif; ?>
                     </select>
                </div>
                 <div class="form-group">
                    <label for="fechaAsignacionMedico">Fecha Asignación Médico:</label>
                    <input type="date" class="form-control" name="fechaAsignacionMedico" id="fechaAsignacionMedico" value="<?php echo htmlspecialchars($servicio['fechaAsignacionMedico'] ?? ''); ?>">
                </div>
                 <div class="form-group">
                    <label for="fechaVisitaMedico">Fecha Visita/Muestras Médico:</label>
                    <input type="date" class="form-control" name="fechaVisitaMedico" id="fechaVisitaMedico" value="<?php echo htmlspecialchars($servicio['fechaVisitaMedico'] ?? ''); ?>">
                </div>
             <?php else: ?>
                  <input type="hidden" name="medico_id" value="">
             <?php endif; ?>

            <div class="form-group">
                <label for="fechaEnvioLG">Fecha Envío a LG (España):</label>
                <input type="date" class="form-control" name="fechaEnvioLG" id="fechaEnvioLG" value="<?php echo htmlspecialchars($servicio['fechaEnvioLG'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="fechaRecepcionLG">Fecha Recepción de LG (España):</label>
                <input type="date" class="form-control" name="fechaRecepcionLG" id="fechaRecepcionLG" value="<?php echo htmlspecialchars($servicio['fechaRecepcionLG'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="fechaFinalizacion">Fecha Finalización (Completado/Rech./Canc.):</label>
                <input type="date" class="form-control" name="fechaFinalizacion" id="fechaFinalizacion" value="<?php echo htmlspecialchars($servicio['fechaFinalizacion'] ?? ''); ?>">
                 <small>Se actualizará automáticamente al marcar un estado final si se deja vacío.</small>
            </div>
             <div class="form-group">
                <label for="descripcion">Observaciones / Notas Internas:</label>
                <textarea class="form-control" name="descripcion" id="descripcion" rows="4"><?php echo htmlspecialchars($servicio['descripcion'] ?? ''); ?></textarea>
            </div>
             <div class="form-group" id="grupo_motivo_rechazo" style="<?php echo (isset($servicio['estado']) && $servicio['estado'] !== 'Rechazado') ? 'display: none;' : ''; ?>">
                <label for="motivo_rechazo">Motivo del Rechazo: <span style="color: red;">*</span></label>
                <textarea class="form-control" name="motivo_rechazo" id="motivo_rechazo" rows="3" <?php echo (isset($servicio['estado']) && $servicio['estado'] === 'Rechazado') ? 'required' : ''; ?>><?php echo htmlspecialchars($servicio['motivo_rechazo'] ?? ''); ?></textarea>
            </div>
        </fieldset> <fieldset>
             <legend>Documentos Asociados a este Servicio</legend>
             <div style="margin-bottom: 20px;">
                 <h4>Documentos Actuales del Servicio:</h4>
                 <?php if (!empty($documentosServicio)): ?>
                     <ul style="list-style: none; padding: 0;">
                         <?php foreach ($documentosServicio as $doc): ?>
                              <li style="border-bottom: 1px solid #eee; padding: 8px 0; display: flex; flex-wrap:wrap; justify-content: space-between; align-items: center; gap: 10px;">
                                  <div style="flex-grow: 1;">
                                     <strong><?php echo htmlspecialchars($doc['tipoDocumento'] ?? 'Desconocido'); ?>:</strong>
                                     <a href="index.php?route=documento_download&id=<?php echo $doc['id_documento']; ?>" target="_blank"> <?php echo htmlspecialchars($doc['nombreArchivoOriginal'] ?? 'Archivo'); ?> </a>
                                     <small>(Subido: <?php echo isset($doc['fechaSubida']) ? date('d/m/Y H:i', strtotime($doc['fechaSubida'])) : 'N/A'; ?> por <?php echo htmlspecialchars($doc['uploaded_by_username'] ?? 'N/A'); ?>)</small>
                                      <?php if(!empty($doc['comentarios'])): ?> <p style="font-size: 0.85em; color: #555;"><i>Comentarios: <?php echo htmlspecialchars($doc['comentarios']); ?></i></p> <?php endif; ?>
                                 </div>
                                  <div style="white-space: nowrap;">
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
             </fieldset> <fieldset>
             <legend>Auditoría</legend>
              <p><small>Registrado por: <?php echo htmlspecialchars($servicio['registrador_username'] ?? 'N/A'); ?> el <?php echo isset($servicio['fecha_registro']) ? date('d/m/Y H:i', strtotime($servicio['fecha_registro'])) : 'N/A'; ?></small></p>
              <p><small>Última modificación por: <?php echo htmlspecialchars($servicio['modificador_username'] ?? 'N/A'); ?> el <?php echo isset($servicio['fecha_modificacion']) ? date('d/m/Y H:i', strtotime($servicio['fecha_modificacion'])) : 'N/A'; ?></small></p>
        </fieldset> <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Guardar Cambios Servicio</button>
            <a href="index.php?route=servicios_index" class="btn btn-secondary">Volver al Listado</a>
             <?php if (isset($servicio['estado']) && !in_array($servicio['estado'], ['Cancelado', 'Completado', 'Rechazado'])): ?>
                 <a href="index.php?route=servicios_cancel&id=<?php echo $servicio['id_servicio']; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de CANCELAR este servicio?')" style="float:right;">Cancelar Servicio</a>
             <?php endif; ?>
        </div>

    </form>
     <?php else: ?>
        <div class='alert alert-error'>Servicio no encontrado.</div>
        <a href="index.php?route=servicios_index" class="btn btn-secondary">Volver al listado</a>
    <?php endif; ?>
</div> <style>
    fieldset { border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 5px; }
    legend { padding: 0 10px; font-weight: bold; color: #333; width: auto; }
    input[type="file"].form-control { display: block; padding: 6px; }
    input:disabled, select:disabled, textarea:disabled { background-color: #e9ecef; cursor: not-allowed; }
</style>
<script>
 document.addEventListener('DOMContentLoaded', function() {
     const estadoSelect = document.getElementById('estado');
     const grupoMotivoRechazo = document.getElementById('grupo_motivo_rechazo');
     const motivoRechazoTextarea = document.getElementById('motivo_rechazo');

     function toggleMotivoRechazo() {
         if (!estadoSelect || !grupoMotivoRechazo || !motivoRechazoTextarea) return;
         if (estadoSelect.value === 'Rechazado') {
             grupoMotivoRechazo.style.display = 'block';
             motivoRechazoTextarea.required = true;
         } else {
             grupoMotivoRechazo.style.display = 'none';
             motivoRechazoTextarea.required = false;
         }
     }

     if(estadoSelect) {
         estadoSelect.addEventListener('change', toggleMotivoRechazo);
         toggleMotivoRechazo(); // Ejecutar al cargar
     }

     // Poner fecha máxima hoy a los campos de fecha editables
     const today = new Date().toISOString().split('T')[0];
     const dateInputs = ['fechaRecepcionDocs', 'fechaPago', 'fechaAsignacionMedico', 'fechaVisitaMedico', 'fechaEnvioLG', 'fechaRecepcionLG', 'fechaFinalizacion'];
     dateInputs.forEach(id => {
          const input = document.getElementById(id);
          // Verificar que el input exista antes de ponerle el max
          if(input) {
              input.setAttribute('max', today);
          }
     });

 });
</script>