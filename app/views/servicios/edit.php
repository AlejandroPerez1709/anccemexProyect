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

// Preparar datos de tipos de servicio para JS (requiere médico)
// Nota: Esta lógica debería ser movida al controlador si es posible para mantener la vista limpia.
$tiposServicioDataJS = [];
if (!empty($tiposServicioList)) {
    // Re-obtener todos los tipos para tener la columna 'requiere_medico'
    // Idealmente, esto ya viene pre-procesado del controlador.
    $allTiposData = TipoServicio::getAll(); /*cite: 2419, 2420, 5441, 5442*/ 
    foreach($allTiposData as $tipo) {
        if (isset($tiposServicioList[$tipo['id_tipo_servicio']])) { // Solo incluir los activos
            $tiposServicioDataJS[$tipo['id_tipo_servicio']] = [
                'req_medico' => !empty($tipo['requiere_medico'])
            ];
        }
    }
}
?>

<div class="form-container form-wide"> <h2>Detalle / Edición Servicio #<?php echo htmlspecialchars($servicio['id_servicio'] ?? 'N/A'); ?></h2>
    <?php if ($servicio): // Solo mostrar formulario si $servicio tiene datos ?>
    <form action="index.php?route=servicios_update" method="POST" id="servicioEditForm" enctype="multipart/form-data">
        <input type="hidden" name="id_servicio" value="<?php echo htmlspecialchars($servicio['id_servicio'] ?? ''); ?>">
        <input type="hidden" name="socio_id" value="<?php echo htmlspecialchars($servicio['socio_id'] ?? ''); ?>">
        <input type="hidden" name="tipo_servicio_id" value="<?php echo htmlspecialchars($servicio['tipo_servicio_id'] ?? ''); ?>">
        <input type="hidden" name="ejemplar_id" value="<?php echo htmlspecialchars($servicio['ejemplar_id'] ?? ''); ?>">
        
        <div class="form-main-columns"> <div class="form-main-col left-col">
                <fieldset>
                    <legend>Información General</legend>
                    <div class="form-group-full">
                        <h5>Tipo de Servicio:</h5>
                        <input type="text" value="<?php echo htmlspecialchars($servicio['tipo_servicio_nombre'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($servicio['codigo_servicio'] ?: 'S/C'); ?>)" disabled>
                    </div>
                    <div class="form-group-full">
                        <h5>Socio Solicitante:</h5>
                        <input type="text" value="<?php echo htmlspecialchars(($servicio['socio_apPaterno'] ?? '') . ' ' . ($servicio['socio_apMaterno'] ?? '') . ', ' . ($servicio['socio_nombre'] ?? '')); ?> (<?php echo htmlspecialchars($servicio['socio_codigo_ganadero'] ?? 'S/C'); ?>)" disabled>
                    </div>
                    <div class="form-group-full">
                        <h5>Ejemplar:</h5>
                        <input type="text" value="<?php echo !empty($servicio['ejemplar_id']) ? htmlspecialchars(($servicio['ejemplar_nombre'] ?? 'ID:'.$servicio['ejemplar_id']) . ' (' . ($servicio['ejemplar_codigo'] ?? 'S/C') . ')') : 'N/A'; ?>" disabled>
                    </div>
                    <div class="form-group-full">
                        <input type="date" name="fechaSolicitud" id="fechaSolicitud"
                               value="<?php echo htmlspecialchars($formData['fechaSolicitud'] ?? ''); ?>"
                               placeholder=" " readonly> <label for="fechaSolicitud">Fecha Solicitud:</label>
                    </div>
                </fieldset>
                
                <fieldset>
                    <legend>Estado y Seguimiento</legend>
                    <div class="form-group-full">
                        <select name="estado" id="estado" required>
                            <option value="" disabled <?php echo empty($formData['estado']) ? 'selected' : ''; ?>>-- Seleccione un estado --</option> <?php foreach($posiblesEstados as $est): ?>
                                <option value="<?php echo htmlspecialchars($est); ?>" <?php echo (isset($formData['estado']) && $formData['estado'] === $est) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($est); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="estado">Estado Actual:<span class="text-danger">*</span></label>
                    </div>
                    <div class="form-group-full">
                        <input type="date" name="fechaRecepcionDocs" id="fechaRecepcionDocs"
                               value="<?php echo htmlspecialchars($formData['fechaRecepcionDocs'] ?? ''); ?>"
                               placeholder=" ">
                        <label for="fechaRecepcionDocs">Fecha Recepción Docs Completos:</label>
                    </div>
                    <div class="form-group-full">
                        <input type="date" name="fechaPago" id="fechaPago"
                               value="<?php echo htmlspecialchars($formData['fechaPago'] ?? ''); ?>"
                               placeholder=" ">
                        <label for="fechaPago">Fecha Registro Pago:</label>
                    </div>
                    <div class="form-group-full">
                        <input type="text" name="referencia_pago" id="referencia_pago"
                               value="<?php echo htmlspecialchars($formData['referencia_pago'] ?? ''); ?>"
                               placeholder=" " maxlength="100" pattern="[A-Za-z0-9\-]+" title="Solo letras, números y guiones">
                        <label for="referencia_pago">Referencia de Pago:</label>
                    </div>

                    <?php 
                    // Si el tipo de servicio original requiere médico, mostrar estos campos.
                    $requiereMedicoActual = !empty($servicio['requiere_medico']);
                    ?>
                    <div class="form-group-full" id="grupo_medico_edit" <?php echo $requiereMedicoActual ? '' : 'style="display: none;"'; ?>>
                        <select name="medico_id" id="medico_id" <?php echo $requiereMedicoActual ? '' : 'disabled'; ?>>
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
                        <label for="medico_id">Médico Asignado:</label>
                    </div>
                    <div class="form-group-full" id="grupo_fechaAsignacionMedico" <?php echo $requiereMedicoActual ? '' : 'style="display: none;"'; ?>>
                        <input type="date" name="fechaAsignacionMedico" id="fechaAsignacionMedico"
                               value="<?php echo htmlspecialchars($formData['fechaAsignacionMedico'] ?? ''); ?>"
                               placeholder=" " <?php echo $requiereMedicoActual ? '' : 'disabled'; ?>>
                        <label for="fechaAsignacionMedico">Fecha Asignación Médico:</label>
                    </div>
                    <div class="form-group-full" id="grupo_fechaVisitaMedico" <?php echo $requiereMedicoActual ? '' : 'style="display: none;"'; ?>>
                        <input type="date" name="fechaVisitaMedico" id="fechaVisitaMedico"
                               value="<?php echo htmlspecialchars($formData['fechaVisitaMedico'] ?? ''); ?>"
                               placeholder=" " <?php echo $requiereMedicoActual ? '' : 'disabled'; ?>>
                        <label for="fechaVisitaMedico">Fecha Visita/Muestras Médico:</label>
                    </div>

                    <div class="form-group-full">
                        <input type="date" name="fechaEnvioLG" id="fechaEnvioLG"
                               value="<?php echo htmlspecialchars($formData['fechaEnvioLG'] ?? ''); ?>"
                               placeholder=" ">
                        <label for="fechaEnvioLG">Fecha Envío a LG (España):</label>
                    </div>
                    <div class="form-group-full">
                        <input type="date" name="fechaRecepcionLG" id="fechaRecepcionLG"
                               value="<?php echo htmlspecialchars($formData['fechaRecepcionLG'] ?? ''); ?>"
                               placeholder=" ">
                        <label for="fechaRecepcionLG">Fecha Recepción de LG (España):</label>
                    </div>

                    <div class="form-group-full">
                        <input type="date" name="fechaFinalizacion" id="fechaFinalizacion"
                               value="<?php echo htmlspecialchars($formData['fechaFinalizacion'] ?? ''); ?>"
                               placeholder=" ">
                        <label for="fechaFinalizacion">Fecha Finalización (Completado/Rech./Canc.):</label>
                        <small>Se actualizará automáticamente al marcar un estado final si se deja vacío.</small>
                    </div>
                    <div class="form-group-full">
                        <textarea name="descripcion" id="descripcion" rows="4" placeholder=" "><?php echo htmlspecialchars($formData['descripcion'] ?? ''); ?></textarea>
                        <label for="descripcion">Observaciones / Notas Internas:</label>
                    </div>
                    <div class="form-group-full" id="grupo_motivo_rechazo" <?php echo (isset($formData['estado']) && $formData['estado'] !== 'Rechazado') ? 'style="display: none;"' : ''; ?>>
                        <textarea name="motivo_rechazo" id="motivo_rechazo" rows="3" placeholder=" " <?php echo (isset($formData['estado']) && $formData['estado'] === 'Rechazado') ? 'required' : ''; ?>><?php echo htmlspecialchars($formData['motivo_rechazo'] ?? ''); ?></textarea>
                        <label for="motivo_rechazo">Motivo del Rechazo:<span class="text-danger">*</span></label>
                    </div>
                </fieldset>
            </div>
            
            <div class="form-main-col right-col">
                <fieldset>
                    <legend>Documentos Asociados a este Servicio</legend>
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
                    <hr>

                    <h4>Subir/Actualizar Documentos del Servicio:</h4>
                    <div class="form-group-full">
                        <label for="solicitud_file">Cambiar Solicitud de Servicio (Firmada):<span class="text-danger">*</span></label>
                        <input type="file" name="solicitud_file" id="solicitud_file"  accept=".pdf,.jpg,.jpeg,.png,.gif">
                    </div>
                    <div class="form-group-full">
                        <label for="pago_file">Cambiar Comprobante de Pago:<span class="text-danger">*</span></label>
                        <input type="file" name="pago_file" id="pago_file"  accept=".pdf,.jpg,.jpeg,.png,.gif">
                    </div>
                </fieldset>
                
                <fieldset>
                    <legend>Auditoría</legend>
                    <div class="form-group-full">
                        <h5>Registrado por:</h5>
                        <input type="text" value="<?php echo htmlspecialchars($servicio['registrador_username'] ?? 'N/A'); ?> el <?php echo isset($servicio['fecha_registro']) ? date('d/m/Y H:i', strtotime($servicio['fecha_registro'])) : 'N/A'; ?>" disabled>
                    </div>
                    <div class="form-group-full">
                        <h5>Última modificación por:</h5>
                        <input type="text" value="<?php echo htmlspecialchars($servicio['modificador_username'] ?? 'N/A'); ?> el <?php echo isset($servicio['fecha_modificacion']) ? date('d/m/Y H:i', strtotime($servicio['fecha_modificacion'])) : 'N/A'; ?>" disabled>
                    </div>
                </fieldset>

                <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>
                <div class="form-actions-bottom">
                    <button type="submit" class="btn btn-primary">Guardar Cambios Servicio</button>
                    <a href="index.php?route=servicios_index" class="btn btn-secondary">Volver al Listado</a>
                    <?php if (isset($servicio['estado']) && !in_array($servicio['estado'], ['Cancelado', 'Completado', 'Rechazado'])): ?>
                        <a href="index.php?route=servicios_cancel&id=<?php echo htmlspecialchars($servicio['id_servicio']); ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('¿Estás seguro de CANCELAR este servicio? Esta acción no se puede deshacer y el estado cambiará a Cancelado.')">Cancelar Servicio</a>
                    <?php endif; ?>
                </div>
            </div>
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

     // Script para mostrar/ocultar campos de médico basado en el tipo de servicio (si el tipo de servicio actual requiere médico)
     // Esto no cambia dinámicamente el campo de médico si el tipo de servicio es de solo lectura.
     // Solo asegura que los campos de médico se muestren correctamente en la carga inicial
     // si el tipo de servicio actual del servicio lo requiere.
     const requiereMedicoActual = <?php echo json_encode(!empty($servicio['requiere_medico'])); ?>;
     const grupoMedicoEdit = document.getElementById('grupo_medico_edit');
     const medicoSelect = document.getElementById('medico_id');
     const fechaAsignacionMedicoInput = document.getElementById('fechaAsignacionMedico');
     const grupoFechaAsignacionMedico = fechaAsignacionMedicoInput ? fechaAsignacionMedicoInput.parentElement : null;
     const fechaVisitaMedicoInput = document.getElementById('fechaVisitaMedico');
     const grupoFechaVisitaMedico = fechaVisitaMedicoInput ? fechaVisitaMedicoInput.parentElement : null;

     function toggleMedicoFieldsInitialVisibility() {
        if (grupoMedicoEdit) {
            grupoMedicoEdit.style.display = requiereMedicoActual ? 'block' : 'none';
        }
        if (grupoFechaAsignacionMedico) {
            grupoFechaAsignacionMedico.style.display = requiereMedicoActual ? 'block' : 'none';
        }
        if (grupoFechaVisitaMedico) {
            grupoFechaVisitaMedico.style.display = requiereMedicoActual ? 'block' : 'none';
        }
     }
     toggleMedicoFieldsInitialVisibility();

     // Deshabilitar/habilitar campos de médico si el tipo de servicio no lo requiere.
     // Se asume que el médico_id del servicio YA ESTÁ ASIGNADO en el hidden.
     if (medicoSelect) {
         medicoSelect.disabled = !requiereMedicoActual;
         if (fechaAsignacionMedicoInput) fechaAsignacionMedicoInput.disabled = !requiereMedicoActual;
         if (fechaVisitaMedicoInput) fechaVisitaMedicoInput.disabled = !requiereMedicoActual;
     }

 });
</script>