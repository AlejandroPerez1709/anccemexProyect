<?php
//app/views/servicios/edit.php

// Asegurar que las variables existan y sean del tipo esperado
// $servicio contendrá los datos del servicio si se cargó correctamente.
// $formData tendrá prioridad si hubo un error de validación en el POST.
$servicio = $servicio ?? null;
$tiposServicioList = $tiposServicioList ?? [];
$sociosList = $sociosList ?? [];
$ejemplares = $ejemplares ?? []; // Lista completa para filtrar select si se habilita
$medicosList = $medicosList ?? [];
$posiblesEstados = $posiblesEstados ?? []; // Debe venir del controlador
$documentosServicio = $documentosServicio ?? []; // Documentos asociados a ESTE servicio
$formData = $formData ?? $servicio; // Repoblar con datos del servicio o de la sesión si hubo error

// Preparar datos de tipos de servicio para JS (requiere médico) - duplicado de create(), se podría refactorizar
$tiposServicioDataJS = [];
if (!empty($tiposServicioList)) {
    $allTiposData = TipoServicio::getAll(); // Necesitamos info completa de todos los tipos para el JS
    foreach($allTiposData as $tipo) {
        if (isset($tiposServicioList[$tipo['id_tipo_servicio']])) { // Solo incluir activos
            $tiposServicioDataJS[$tipo['id_tipo_servicio']] = [
                'req_medico' => !empty($tipo['requiere_medico'])
            ];
        }
    }
}
?>

<h2>Detalle / Edición Servicio #<?php echo htmlspecialchars($servicio['id_servicio'] ?? 'N/A'); ?></h2>

<?php
// Mensajes de error y éxito ahora se gestionan en master.php.
// Eliminamos la lógica duplicada aquí.
?>

<div class="form-container">
    <?php if ($servicio): // Solo mostrar formulario si $servicio tiene datos ?>
    <form action="index.php?route=servicios_update" method="POST" id="servicioEditForm" enctype="multipart/form-data">
        <input type="hidden" name="id_servicio" value="<?php echo htmlspecialchars($servicio['id_servicio'] ?? ''); ?>">
        <input type="hidden" name="socio_id" value="<?php echo htmlspecialchars($servicio['socio_id'] ?? ''); ?>">
        <input type="hidden" name="tipo_servicio_id" value="<?php echo htmlspecialchars($servicio['tipo_servicio_id'] ?? ''); ?>">
        <input type="hidden" name="ejemplar_id" value="<?php echo htmlspecialchars($servicio['ejemplar_id'] ?? ''); ?>">
        
        <fieldset>
            <legend>Información General</legend>
            <div class="form-group">
                <label>Tipo de Servicio:</label>
                <input type="text" class="form-control" 
                       value="<?php echo htmlspecialchars($servicio['tipo_servicio_nombre'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($servicio['codigo_servicio'] ?: 'S/C'); ?>)" 
                       disabled>
            </div>
            <div class="form-group">
                <label>Socio Solicitante:</label>
                <input type="text" class="form-control" 
                       value="<?php echo htmlspecialchars(($servicio['socio_apPaterno'] ?? '') . ' ' . ($servicio['socio_apMaterno'] ?? '') . ', ' . ($servicio['socio_nombre'] ?? '')); ?> (<?php echo htmlspecialchars($servicio['socio_codigo_ganadero'] ?? 'S/C'); ?>)" 
                       disabled>
            </div>
            <div class="form-group">
                <label>Ejemplar:</label>
                 <input type="text" class="form-control" 
                        value="<?php echo !empty($servicio['ejemplar_id']) ? htmlspecialchars(($servicio['ejemplar_nombre'] ?? 'ID:'.$servicio['ejemplar_id']) . ' (' . ($servicio['ejemplar_codigo'] ?? 'S/C') . ')') : 'N/A'; ?>" 
                        disabled>
            </div>
             <div class="form-group">
                <label for="fechaSolicitud">Fecha Solicitud:</label>
                <input type="date" class="form-control" name="fechaSolicitud" id="fechaSolicitud" 
                       value="<?php echo htmlspecialchars($formData['fechaSolicitud'] ?? ''); ?>" 
                       readonly>
            </div>
        </fieldset>
        
        <fieldset>
            <legend>Estado y Seguimiento</legend>
             <div class="form-group">
                 <label for="estado">Estado Actual: <span class="text-danger">*</span></label>
                 <select class="form-control" name="estado" id="estado" required>
                      <?php foreach($posiblesEstados as $est): ?>
                         <option value="<?php echo htmlspecialchars($est); ?>" <?php echo (isset($formData['estado']) && $formData['estado'] === $est) ? 'selected' : ''; ?>>
                             <?php echo htmlspecialchars($est); ?>
                         </option>
                     <?php endforeach; ?>
                 </select>
            </div>
             <div class="form-group">
                <label for="fechaRecepcionDocs">Fecha Recepción Docs Completos:</label>
                <input type="date" class="form-control" name="fechaRecepcionDocs" id="fechaRecepcionDocs" 
                       value="<?php echo htmlspecialchars($formData['fechaRecepcionDocs'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="fechaPago">Fecha Registro Pago:</label>
                <input type="date" class="form-control" name="fechaPago" id="fechaPago" 
                       value="<?php echo htmlspecialchars($formData['fechaPago'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="referencia_pago">Referencia de Pago:</label>
                <input type="text" class="form-control" name="referencia_pago" id="referencia_pago" 
                       value="<?php echo htmlspecialchars($formData['referencia_pago'] ?? ''); ?>" 
                       maxlength="100" pattern="[A-Za-z0-9\-]+" title="Solo letras, números y guiones">
            </div>

            <?php 
            // La visibilidad de este grupo depende de si el tipo de servicio requiere médico (del servicio original)
            $requiereMedicoActual = !empty($servicio['requiere_medico']);
            ?>
            <div class="form-group" id="grupo_medico_edit" style="<?php echo $requiereMedicoActual ? '' : 'display: none;'; ?>">
                 <label for="medico_id">Médico Asignado:</label>
                 <select class="form-control" name="medico_id" id="medico_id" <?php echo $requiereMedicoActual ? '' : 'disabled'; ?>>
                     <option value="" selected>-- Sin Asignar --</option>
                          <?php foreach ($medicosList as $id_med => $display_med): ?>
                            <option value="<?php echo htmlspecialchars($id_med); ?>" <?php echo (isset($formData['medico_id']) && $formData['medico_id'] == $id_med) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($display_med); ?>
                            </option>
                        <?php endforeach; ?>
                         <?php 
                         // Si el médico actual del servicio no está en la lista de activos, mostrarlo como opción
                         // Esto puede pasar si el médico se ha inactivado.
                         if (!empty($servicio['medico_id']) && !isset($medicosList[$servicio['medico_id']])): 
                         ?>
                               <option value="<?php echo htmlspecialchars($servicio['medico_id']); ?>" selected>
                                   <?php echo htmlspecialchars(($servicio['medico_nombre'] ?? 'ID:'.$servicio['medico_id']) . ' ' . ($servicio['medico_apPaterno'] ?? '')) ?> [Actual, Inactivo?]
                               </option>
                          <?php endif; ?>
                     </select>
                </div>
                 <div class="form-group" id="grupo_fechaAsignacionMedico" style="<?php echo $requiereMedicoActual ? '' : 'display: none;'; ?>">
                   <label for="fechaAsignacionMedico">Fecha Asignación Médico:</label>
                    <input type="date" class="form-control" name="fechaAsignacionMedico" id="fechaAsignacionMedico" 
                           value="<?php echo htmlspecialchars($formData['fechaAsignacionMedico'] ?? ''); ?>"
                           <?php echo $requiereMedicoActual ? '' : 'disabled'; ?>>
                </div>
                 <div class="form-group" id="grupo_fechaVisitaMedico" style="<?php echo $requiereMedicoActual ? '' : 'display: none;'; ?>">
                    <label for="fechaVisitaMedico">Fecha Visita/Muestras Médico:</label>
                    <input type="date" class="form-control" name="fechaVisitaMedico" id="fechaVisitaMedico" 
                           value="<?php echo htmlspecialchars($formData['fechaVisitaMedico'] ?? ''); ?>"
                           <?php echo $requiereMedicoActual ? '' : 'disabled'; ?>>
                </div>

            <div class="form-group">
                <label for="fechaEnvioLG">Fecha Envío a LG (España):</label>
                <input type="date" class="form-control" name="fechaEnvioLG" id="fechaEnvioLG" 
                       value="<?php echo htmlspecialchars($formData['fechaEnvioLG'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="fechaRecepcionLG">Fecha Recepción de LG (España):</label>
                <input type="date" class="form-control" name="fechaRecepcionLG" id="fechaRecepcionLG" 
                       value="<?php echo htmlspecialchars($formData['fechaRecepcionLG'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="fechaFinalizacion">Fecha Finalización (Completado/Rech./Canc.):</label>
                <input type="date" class="form-control" name="fechaFinalizacion" id="fechaFinalizacion" 
                       value="<?php echo htmlspecialchars($formData['fechaFinalizacion'] ?? ''); ?>">
                 <small>Se actualizará automáticamente al marcar un estado final si se deja vacío.</small>
            </div>
             <div class="form-group">
                <label for="descripcion">Observaciones / Notas Internas:</label>
                <textarea class="form-control" name="descripcion" id="descripcion" rows="4"><?php echo htmlspecialchars($formData['descripcion'] ?? ''); ?></textarea>
            </div>
             <div class="form-group" id="grupo_motivo_rechazo" style="<?php echo (isset($formData['estado']) && $formData['estado'] !== 'Rechazado') ? 'display: none;' : ''; ?>">
                <label for="motivo_rechazo">Motivo del Rechazo: <span class="text-danger">*</span></label>
                <textarea class="form-control" name="motivo_rechazo" id="motivo_rechazo" rows="3" <?php echo (isset($formData['estado']) && $formData['estado'] === 'Rechazado') ? 'required' : ''; ?>><?php echo htmlspecialchars($formData['motivo_rechazo'] ?? ''); ?></textarea>
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
                                     <a href="index.php?route=documento_download&id=<?php echo htmlspecialchars($doc['id_documento']); ?>" target="_blank" title="Descargar <?php echo htmlspecialchars($doc['nombreArchivoOriginal']); ?>">
                                         <?php echo htmlspecialchars($doc['nombreArchivoOriginal'] ?? 'Archivo'); ?>
                                     </a>
                                     <small>(Subido: <?php echo isset($doc['fechaSubida']) ? date('d/m/Y H:i', strtotime($doc['fechaSubida'])) : 'N/A'; ?> por <?php echo htmlspecialchars($doc['uploaded_by_username'] ?? 'N/A'); ?>)</small>
                                      <?php if(!empty($doc['comentarios'])): ?> 
                                         <p class="document-comment"><i>Comentarios: <?php echo htmlspecialchars($doc['comentarios']); ?></i></p> 
                                     <?php endif; ?>
                                 </div>
                                  <div class="document-list-item-actions">
                                      <?php if (isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'superusuario'): ?>
                                         <a href="index.php?route=documento_delete&id=<?php echo htmlspecialchars($doc['id_documento']); ?>&servicio_id=<?php echo htmlspecialchars($servicio['id_servicio']); ?>" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Seguro que quieres eliminar el documento \'<?php echo htmlspecialchars(addslashes($doc['nombreArchivoOriginal'])); ?>\'?\n¡Esta acción borrará el archivo permanentemente!')">Eliminar</a>
                                      <?php endif; ?>
                                 </div>
                              </li>
                         <?php endforeach; ?>
                     </ul>
                 <?php else: ?> 
                   <p>No hay documentos maestros registrados para este servicio.</p> 
                 <?php endif; ?>
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
        
        <div class="form-actions"> <button type="submit" class="btn btn-primary">Guardar Cambios Servicio</button>
            <a href="index.php?route=servicios_index" class="btn btn-secondary">Volver al Listado</a>
             <?php if (isset($servicio['estado']) && !in_array($servicio['estado'], ['Cancelado', 'Completado', 'Rechazado'])): ?>
                 <a href="index.php?route=servicios_cancel&id=<?php echo htmlspecialchars($servicio['id_servicio']); ?>" 
                    class="btn btn-danger float-right" 
                    onclick="return confirm('¿Estás seguro de CANCELAR este servicio? Esta acción no se puede deshacer y el estado cambiará a Cancelado.')">Cancelar Servicio</a>
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
     const grupoMotivoRechazo = document.getElementById('grupo_motivo_rechazo');
     const motivoRechazoTextarea = document.getElementById('motivo_rechazo');
     const tipoServicioOriginalRequiereMedico = <?php echo json_encode(!empty($servicio['requiere_medico'])); ?>; // Booleano desde PHP

     // CAMPOS RELACIONADOS AL MÉDICO (VISIBILIDAD)
     const grupoMedicoEdit = document.getElementById('grupo_medico_edit');
     const medicoSelect = document.getElementById('medico_id');
     const grupoFechaAsignacionMedico = document.getElementById('grupo_fechaAsignacionMedico');
     const fechaAsignacionMedicoInput = document.getElementById('fechaAsignacionMedico');
     const grupoFechaVisitaMedico = document.getElementById('grupo_fechaVisitaMedico');
     const fechaVisitaMedicoInput = document.getElementById('fechaVisitaMedico');


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

     function toggleMedicoFieldsVisibility() {
         // La visibilidad de los campos de médico en edición depende del tipo de servicio original
         // No cambia dinámicamente si se cambia el tipo de servicio (que es de solo lectura aquí)
         // Solo se muestra/oculta si el servicio original lo requiere.
         if (grupoMedicoEdit) {
             grupoMedicoEdit.style.display = tipoServicioOriginalRequiereMedico ? 'block' : 'none';
             if (medicoSelect) medicoSelect.disabled = !tipoServicioOriginalRequiereMedico;
         }
         if (grupoFechaAsignacionMedico) {
             grupoFechaAsignacionMedico.style.display = tipoServicioOriginalRequiereMedico ? 'block' : 'none';
             if (fechaAsignacionMedicoInput) fechaAsignacionMedicoInput.disabled = !tipoServicioOriginalRequiereMedico;
         }
         if (grupoFechaVisitaMedico) {
             grupoFechaVisitaMedico.style.display = tipoServicioOriginalRequiereMedico ? 'block' : 'none';
             if (fechaVisitaMedicoInput) fechaVisitaMedicoInput.disabled = !tipoServicioOriginalRequiereMedico;
         }
     }


     if(estadoSelect) {
         estadoSelect.addEventListener('change', toggleMotivoRechazo);
         toggleMotivoRechazo(); // Ejecutar al cargar para establecer el estado inicial
     }

     // Ejecutar al cargar para establecer el estado inicial de los campos de médico
     toggleMedicoFieldsVisibility();

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