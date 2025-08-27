<?php
//app/views/servicios/edit.php
$servicio = $servicio ?? null;
$tiposServicioList = $tiposServicioList ?? [];
$sociosList = $sociosList ?? [];
$ejemplares = $ejemplares ?? [];
$medicosList = $medicosList ?? [];
$posiblesEstados = $posiblesEstados ?? [];
$documentosServicio = $documentosServicio ?? [];
$historialServicio = $historialServicio ?? [];
$formData = $formData ?? $servicio;
?>

<div class="form-container form-wide"> <h2>Detalle / Edición Servicio #<?php echo htmlspecialchars($servicio['id_servicio'] ?? 'N/A'); ?></h2>
    <?php if ($servicio): ?>
    <form action="index.php?route=servicios_update" method="POST" id="servicioEditForm" enctype="multipart/form-data">
        <input type="hidden" name="id_servicio" value="<?php echo htmlspecialchars($servicio['id_servicio'] ?? ''); ?>">
        <input type="hidden" name="ejemplar_id" value="<?php echo htmlspecialchars($servicio['ejemplar_id'] ?? ''); ?>">

         <div class="form-main-columns"> 
             <div class="form-main-col left-col">
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
                         <input type="date" name="fechaSolicitud" id="fechaSolicitud" value="<?php echo htmlspecialchars($formData['fechaSolicitud'] ?? ''); ?>" placeholder=" " readonly> <label for="fechaSolicitud">Fecha Solicitud:</label>
                    </div>
                </fieldset>
                
                 <fieldset>
                    <legend>Estado y Seguimiento</legend>
                      <div class="form-group-full">
                        <?php
                        $estadosFinales = ['Completado', 'Rechazado', 'Cancelado'];
                        $isFinalizado = in_array($formData['estado'], $estadosFinales);
                        ?>
                        <select name="estado" id="estado" required <?php echo $isFinalizado ? 'disabled' : ''; ?>>
                             <?php foreach($posiblesEstados as $est): ?>
                                <option value="<?php echo htmlspecialchars($est); ?>" <?php echo (isset($formData['estado']) && $formData['estado'] === $est) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($est); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="estado">Estado Actual:<span class="text-danger">*</span></label>
                        <?php if ($isFinalizado): ?>
                            <small>Este servicio se encuentra en un estado final y no puede ser modificado.</small>
                        <?php endif; ?>
                    </div>
                    <div class="form-group-full">
                        <input type="date" name="fechaRecepcionDocs" id="fechaRecepcionDocs" value="<?php echo htmlspecialchars($formData['fechaRecepcionDocs'] ?? ''); ?>" placeholder=" " readonly>
                        <label for="fechaRecepcionDocs">Fecha Recepción Docs Completos:</label>
                    </div>
                    <div class="form-group-full">
                        <input type="date" name="fechaPago" id="fechaPago" value="<?php echo htmlspecialchars($formData['fechaPago'] ?? ''); ?>" placeholder=" " readonly>
                        <label for="fechaPago">Fecha Registro Pago:</label>
                    </div>
                    <div class="form-group-full">
                        <input type="text" name="referencia_pago" id="referencia_pago" value="<?php echo htmlspecialchars($formData['referencia_pago'] ?? ''); ?>" placeholder=" " maxlength="100" pattern="[A-Za-z0-9\-]+" title="Solo letras, números y guiones" <?php echo $isFinalizado ? 'disabled' : ''; ?>>
                        <label for="referencia_pago">Referencia de Pago:</label>
                    </div>

                    <?php $requiereMedicoActual = !empty($servicio['requiere_medico']); ?>
                    <div class="form-group-full" id="grupo_medico_edit" <?php echo $requiereMedicoActual ? '' : 'style="display: none;"'; ?>>
                        <select name="medico_id" id="medico_id" <?php echo $isFinalizado ? 'disabled' : ''; ?>>
                            <option value="">-- Sin Asignar --</option>
                            <?php foreach ($medicosList as $id_med => $display_med): ?>
                                <option value="<?php echo htmlspecialchars($id_med); ?>" <?php echo (isset($formData['medico_id']) && $formData['medico_id'] == $id_med) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($display_med); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if (!empty($servicio['medico_id']) && !isset($medicosList[$servicio['medico_id']])): ?>
                                 <option value="<?php echo htmlspecialchars($servicio['medico_id']); ?>" selected>
                                    <?php echo htmlspecialchars(($servicio['medico_nombre'] ?? 'ID:'.$servicio['medico_id']) . ' ' . ($servicio['medico_apPaterno'] ?? '')) ?> [Actual, Inactivo?]
                                </option>
                            <?php endif; ?>
                        </select>
                        <label for="medico_id">Médico Asignado:</label>
                    </div>
                    <div class="form-group-full" id="grupo_fechaAsignacionMedico" <?php echo $requiereMedicoActual ? '' : 'style="display: none;"'; ?>>
                        <input type="date" name="fechaAsignacionMedico" id="fechaAsignacionMedico" value="<?php echo htmlspecialchars($formData['fechaAsignacionMedico'] ?? ''); ?>" placeholder=" " readonly>
                        <label for="fechaAsignacionMedico">Fecha Asignación Médico:</label>
                    </div>
                     <div class="form-group-full" id="grupo_fechaVisitaMedico" <?php echo $requiereMedicoActual ? '' : 'style="display: none;"'; ?>>
                        <input type="date" name="fechaVisitaMedico" id="fechaVisitaMedico" value="<?php echo htmlspecialchars($formData['fechaVisitaMedico'] ?? ''); ?>" placeholder=" " readonly>
                        <label for="fechaVisitaMedico">Fecha Visita/Muestras Médico:</label>
                    </div>

                     <div class="form-group-full">
                        <input type="date" name="fechaEnvioLG" id="fechaEnvioLG" value="<?php echo htmlspecialchars($formData['fechaEnvioLG'] ?? ''); ?>" placeholder=" " readonly>
                        <label for="fechaEnvioLG">Fecha Envío a LG (España):</label>
                    </div>
                     <div class="form-group-full">
                        <input type="date" name="fechaRecepcionLG" id="fechaRecepcionLG" value="<?php echo htmlspecialchars($formData['fechaRecepcionLG'] ?? ''); ?>" placeholder=" " readonly>
                        <label for="fechaRecepcionLG">Fecha Recepción de LG (España):</label>
                    </div>

                     <div class="form-group-full">
                        <input type="date" name="fechaFinalizacion" id="fechaFinalizacion" value="<?php echo htmlspecialchars($formData['fechaFinalizacion'] ?? ''); ?>" placeholder=" " readonly>
                        <label for="fechaFinalizacion">Fecha Finalización (Completado/Rech./Canc.):</label>
                        <small>Se actualizará automáticamente al marcar un estado final si se deja vacío.</small>
                     </div>
                     <div class="form-group-full">
                        <textarea name="descripcion" id="descripcion" rows="4" placeholder=" " <?php echo $isFinalizado ? 'disabled' : ''; ?>><?php echo htmlspecialchars($formData['descripcion'] ?? ''); ?></textarea>
                        <label for="descripcion">Observaciones / Notas Internas:</label>
                     </div>
                    <div class="form-group-full" id="grupo_motivo_rechazo" <?php echo (isset($formData['estado']) && $formData['estado'] !== 'Rechazado') ? 'style="display: none;"' : ''; ?>>
                        <textarea name="motivo_rechazo" id="motivo_rechazo" rows="3" placeholder=" " <?php echo (isset($formData['estado']) && $formData['estado'] === 'Rechazado') ? 'required' : ''; ?> <?php echo $isFinalizado ? 'disabled' : ''; ?>><?php echo htmlspecialchars($formData['motivo_rechazo'] ?? ''); ?></textarea>
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
                                <li class="document-list-item" id="doc-item-<?php echo $doc['id_documento']; ?>">
                                     <div class="document-list-item-content">
                                        <strong><?php echo htmlspecialchars($doc['tipoDocumento'] ?? 'Desconocido'); ?>:</strong>
                                        
                                        <?php
                                        $isImage = in_array($doc['mimeType'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                                        $linkClass = $isImage ? 'document-link' : '';
                                        $dataAttribute = $isImage ? 'data-is-image="true"' : '';
                                        $targetAttribute = $isImage ? '' : 'target="_blank"';
                                        ?>
                                        <a href="index.php?route=documento_download&id=<?php echo htmlspecialchars($doc['id_documento']); ?>" 
                                           class="<?php echo $linkClass; ?>"
                                           <?php echo $dataAttribute; ?>
                                           <?php echo $targetAttribute; ?>
                                           title="<?php echo htmlspecialchars($doc['nombreArchivoOriginal']); ?>">
                                            <?php echo htmlspecialchars($doc['nombreArchivoOriginal'] ?? 'Archivo'); ?>
                                        </a>

                                        <small>(Subido: <?php echo isset($doc['fechaSubida']) ? date('d/m/Y H:i', strtotime($doc['fechaSubida'])) : 'N/A'; ?> por <?php echo htmlspecialchars($doc['uploaded_by_username'] ?? 'N/A'); ?>)</small>
                                         <?php if(!empty($doc['comentarios'])): ?> 
                                            <p class="document-comment"><i>Comentarios: <?php echo htmlspecialchars($doc['comentarios']); ?></i></p> 
                                        <?php endif; ?>
                                    </div>
                                    <div class="document-list-item-actions">
                                         <?php if (isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'superusuario' && !$isFinalizado): ?>
                                            <a href="index.php?route=documento_delete&id=<?php echo htmlspecialchars($doc['id_documento']); ?>&servicio_id=<?php echo htmlspecialchars($servicio['id_servicio']); ?>" 
                                               class="btn btn-sm btn-danger btn-delete-ajax"
                                               data-doc-id="<?php echo $doc['id_documento']; ?>"
                                               data-doc-name="<?php echo htmlspecialchars(addslashes($doc['nombreArchivoOriginal'])); ?>">Eliminar</a>
                                         <?php endif; ?>
                                         </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?> 
                        <p>No hay documentos maestros registrados para este servicio.</p> 
                    <?php endif; ?>
                    <hr>
                    
                    <?php if (!$isFinalizado): ?>
                        <h4>Subir/Actualizar Documentos del Servicio:</h4>
                        <div class="form-group-full">
                            <label for="solicitud_file">Cambiar Solicitud de Servicio (Firmada):</label>
                            <input type="file" name="solicitud_file" id="solicitud_file"  accept=".pdf,.jpg,.jpeg,.png,.gif">
                        </div>
                        <div class="form-group-full">
                            <label for="pago_file">Cambiar Comprobante de Pago:</label>
                            <input type="file" name="pago_file" id="pago_file"  accept=".pdf,.jpg,.jpeg,.png,.gif">
                        </div>
                    <?php endif; ?>
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
            </div>
        </div>

        <fieldset>
            <legend>Historial del Trámite (Bitácora)</legend>
            <div class="table-container history-table">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                             <th>Usuario</th>
                            <th>Cambio Realizado</th>
                            <th>Comentarios</th>
                        </tr>
                     </thead>
                    <tbody>
                        <?php if (!empty($historialServicio)): ?>
                            <?php foreach ($historialServicio as $registro): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($registro['fecha_cambio'])); ?></td>
                                     <td><?php echo htmlspecialchars($registro['usuario_nombre'] ?? 'Sistema'); ?></td>
                                    <td>
                                        <?php if ($registro['estado_anterior']): ?>
                                             Estado cambió de <strong><?php echo htmlspecialchars($registro['estado_anterior']); ?></strong> a <strong><?php echo htmlspecialchars($registro['estado_nuevo']); ?></strong>
                                        <?php else: ?>
                                            Servicio creado con estado <strong><?php echo htmlspecialchars($registro['estado_nuevo']); ?></strong>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($registro['comentarios'] ?? '--'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay historial de cambios para este servicio.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </fieldset>

        <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>
        <div class="form-actions-bottom">
            <?php if (!$isFinalizado): // Solo mostrar el botón de guardar si no está finalizado ?>
                <button type="submit" class="btn btn-primary">Guardar Cambios Servicio</button>
            <?php endif; ?>
            <a href="index.php?route=servicios_index" class="btn btn-secondary">Volver al Listado</a>
            <?php if (isset($servicio['estado']) && !in_array($servicio['estado'], ['Cancelado', 'Completado', 'Rechazado'])): ?>
                <a href="index.php?route=servicios_cancel&id=<?php echo htmlspecialchars($servicio['id_servicio']); ?>" 
                   class="btn btn-danger" 
                   onclick="return confirm('¿Estás seguro de CANCELAR este servicio? Esta acción no se puede deshacer y el estado cambiará a Cancelado.')">Cancelar Servicio</a>
            <?php endif; ?>
        </div>
    </form>
    <?php else: ?>
        <div class='alert alert-error'>Servicio no encontrado.</div>
        <a href="index.php?route=servicios_index" class="btn btn-secondary">Volver al listado</a>
    <?php endif; ?>
</div>

<script src="<?php echo BASE_URL; ?>/assets/js/servicios-edit.js"></script>