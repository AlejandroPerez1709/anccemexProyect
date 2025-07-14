<?php
// app/views/servicios/create.php

// Recuperar datos del formulario si hubo error. Viene del controlador.
$formData = $formData ?? []; // Asegurar que $formData exista

// Mensajes de error y warning ya se gestionan en master.php.
?>

<h2>Registrar Nueva Solicitud de Servicio</h2>

<div class="form-container">
     <form action="index.php?route=servicios_store" method="POST" id="servicioForm" enctype="multipart/form-data">

        <fieldset>
             <legend>Datos de la Solicitud</legend>
             <div class="form-group">
                 <label for="tipo_servicio_id">Tipo de Servicio: <span class="text-danger">*</span></label>
                 <select class="form-control" name="tipo_servicio_id" id="tipo_servicio_id" required <?php echo empty($tiposServicioList) ? 'disabled' : ''; ?>>
                     <option value="" disabled <?php echo empty($formData['tipo_servicio_id']) ? 'selected' : ''; ?>>-- Seleccione Tipo --</option>
                     <?php foreach ($tiposServicioList as $id => $display): ?>
                         <option value="<?php echo htmlspecialchars($id); ?>"
                                 <?php echo (isset($formData['tipo_servicio_id']) && $formData['tipo_servicio_id'] == $id) ? 'selected' : ''; ?>
                                 data-req-medico="<?php echo isset($tiposServicioDataJS[$id]) && $tiposServicioDataJS[$id]['req_medico'] ? '1' : '0'; ?>">
                             <?php echo htmlspecialchars($display); ?>
                         </option>
                     <?php endforeach; ?>
                 </select>
                 <?php if (empty($tiposServicioList)): ?><small class="text-danger">No hay tipos de servicio activos definidos.</small><?php endif; ?>
             </div>

             <div class="form-group">
                 <label for="socio_id">Socio Solicitante: <span class="text-danger">*</span></label>
                 <select class="form-control" name="socio_id" id="socio_id" required <?php echo empty($sociosList) ? 'disabled' : ''; ?>>
                      <option value="" disabled <?php echo empty($formData['socio_id']) ? 'selected' : ''; ?>>-- Seleccione Socio --</option>
                      <?php foreach ($sociosList as $id => $display): ?>
                         <option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($formData['socio_id']) && $formData['socio_id'] == $id) ? 'selected' : ''; ?>>
                             <?php echo htmlspecialchars($display); ?>
                         </option>
                     <?php endforeach; ?>
                 </select>
                  <?php if (empty($sociosList)): ?><small class="text-danger">No hay socios activos registrados.</small><?php endif; ?>
             </div>

             <div class="form-group" id="grupo_ejemplar">
                 <label for="ejemplar_id">Ejemplar: <span class="text-danger">*</span></label>
                 <select class="form-control" name="ejemplar_id" id="ejemplar_id" required disabled>
                     <option value="" selected disabled>-- Seleccione un Socio Primero --</option>
                     <?php foreach ($ejemplares as $ejemplar): ?>
                          <option value="<?php echo htmlspecialchars($ejemplar['id_ejemplar']); ?>"
                                  data-socio="<?php echo htmlspecialchars($ejemplar['socio_id']); ?>"
                                  class="hidden" /* Se usa clase para ocultar/mostrar con JS */
                                  <?php echo (isset($formData['ejemplar_id']) && $formData['ejemplar_id'] == $ejemplar['id_ejemplar']) ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($ejemplar['nombre'] . ' (' . ($ejemplar['codigo_ejemplar'] ?: 'S/C') . ')'); ?>
                          </option>
                      <?php endforeach; ?>
                      <option value="" disabled data-socio="0" class="no-ejemplares-option hidden text-danger">-- Este socio no tiene ejemplares --</option>
                 </select>
                  <small id="ejemplar_msg">Seleccione un socio para ver sus ejemplares.</small>
             </div>

             <div class="form-group hidden" id="grupo_medico">
                 <label for="medico_id">Médico Asignado (Opcional al inicio):</label>
                  <select class="form-control" name="medico_id" id="medico_id">
                      <option value="" selected>-- Sin Asignar --</option>
                       <?php foreach ($medicosList as $id => $display): ?>
                         <option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($formData['medico_id']) && $formData['medico_id'] == $id) ? 'selected' : ''; ?>>
                             <?php echo htmlspecialchars($display); ?>
                         </option>
                     <?php endforeach; ?>
                  </select>
             </div>

              <div class="form-group">
                 <label for="fechaSolicitud">Fecha de Solicitud: <span class="text-danger">*</span></label>
                 <input type="date" class="form-control" name="fechaSolicitud" id="fechaSolicitud" 
                        value="<?php echo htmlspecialchars($formData['fechaSolicitud'] ?? date('Y-m-d')); ?>" 
                        required max="<?php echo date('Y-m-d'); ?>">
              </div>

             <div class="form-group">
                 <label for="descripcion">Descripción / Notas Iniciales:</label>
                 <textarea class="form-control" name="descripcion" id="descripcion" rows="3"><?php echo htmlspecialchars($formData['descripcion'] ?? ''); ?></textarea>
             </div>
        </fieldset>

        <fieldset>
             <legend>Documentos Iniciales</legend>
              <div class="form-group">
                 <label for="solicitud_file">Adjuntar Solicitud de Servicio (Firmada): <span class="text-danger">*</span></label>
                 <input type="file" class="form-control" name="solicitud_file" id="solicitud_file" required accept=".pdf,.jpg,.jpeg,.png,.gif">
                 <small>Archivo PDF o imagen legible.</small>
             </div>
              <div class="form-group">
                 <label for="pago_file">Adjuntar Comprobante de Pago: <span class="text-danger">*</span></label>
                 <input type="file" class="form-control" name="pago_file" id="pago_file" required accept=".pdf,.jpg,.jpeg,.png,.gif">
             </div>
              <div class="form-group">
                 <label for="referencia_pago">Referencia de Pago (Folio, Transacción, etc.):</label>
                 <input type="text" class="form-control" name="referencia_pago" id="referencia_pago" 
                        value="<?php echo htmlspecialchars($formData['referencia_pago'] ?? ''); ?>" 
                        maxlength="100" pattern="[A-Za-z0-9\-]+" title="Solo letras, números y guiones">
             </div>
         </fieldset>

        <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>
        <button type="submit" class="btn btn-primary" <?php echo (empty($sociosList) || empty($tiposServicioList)) ? 'disabled' : ''; ?>>Registrar Servicio</button>
        <a href="index.php?route=servicios_index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipoServicioSelect = document.getElementById('tipo_servicio_id');
        const socioSelect = document.getElementById('socio_id');
        const ejemplarSelect = document.getElementById('ejemplar_id');
        const medicoSelect = document.getElementById('medico_id');
        const grupoMedico = document.getElementById('grupo_medico');
        const ejemplarMsg = document.getElementById('ejemplar_msg');
        const noEjemplaresOption = ejemplarSelect.querySelector('.no-ejemplares-option');
        
        // Convertir datos PHP a JS de forma segura
        const tiposServicioInfo = <?php echo json_encode($tiposServicioDataJS); ?>;

        function actualizarVisibilidadYFiltros() {
            const tipoId = tipoServicioSelect.value;
            const socioId = socioSelect.value;
            let requiereMedico = false;

            // Visibilidad Grupo Médico
            if (tipoId && tiposServicioInfo[tipoId]) {
                requiereMedico = tiposServicioInfo[tipoId].req_medico;
            }
            grupoMedico.classList.toggle('hidden', !requiereMedico); // Usa toggle para añadir/quitar clase 'hidden'
            // Si el médico ya no es requerido, asegurar que el campo medico_id no tenga un valor previo
            if (!requiereMedico) {
                medicoSelect.value = ''; // Resetea la selección si se oculta
            }

            // Habilitar/Deshabilitar y Filtrar Ejemplares
            if (socioId) {
                ejemplarSelect.disabled = false;
                ejemplarMsg.textContent = 'Seleccione el ejemplar para el servicio.';
                ejemplarMsg.classList.remove('text-danger');
                let opcionesVisibles = 0;
                
                // Mostrar/ocultar opciones de ejemplares
                Array.from(ejemplarSelect.options).forEach(option => {
                    if (option.value === "" || option.classList.contains('no-ejemplares-option')) {
                        option.classList.add('hidden'); 
                        return;
                    }
                    if (option.dataset.socio == socioId) {
                        option.classList.remove('hidden');
                        opcionesVisibles++;
                    } else {
                        option.classList.add('hidden');
                        // Si la opción seleccionada actualmente se oculta, deseleccionar
                        if (option.selected) { ejemplarSelect.value = ""; } 
                    }
                });
                
                // Manejar caso donde no hay ejemplares para el socio
                if (opcionesVisibles === 0) {
                    ejemplarSelect.disabled = true;
                    if(noEjemplaresOption) noEjemplaresOption.classList.remove('hidden');
                    ejemplarSelect.value = ""; // Asegurarse de que no haya nada seleccionado
                    ejemplarMsg.textContent = 'Este socio no tiene ejemplares registrados.';
                    ejemplarMsg.classList.add('text-danger');
                } else {
                    // Si el socio ha cambiado y había un ejemplar seleccionado que ya no pertenece
                    // o si el ejemplar actual no es del nuevo socio, deseleccionar
                    const selectedOption = ejemplarSelect.options[ejemplarSelect.selectedIndex];
                    if(selectedOption && selectedOption.classList.contains('hidden')){
                           ejemplarSelect.value = "";
                    }
                    if(noEjemplaresOption) noEjemplaresOption.classList.add('hidden');
                }
            } else {
                // Si no hay socio seleccionado, deshabilitar ejemplares y resetear
                ejemplarSelect.disabled = true;
                ejemplarSelect.value = "";
                ejemplarMsg.textContent = 'Seleccione un socio para ver sus ejemplares.';
                ejemplarMsg.classList.remove('text-danger');
                Array.from(ejemplarSelect.options).forEach(option => {
                     if(option.value !== "" && !option.disabled) option.classList.add('hidden');
                });
                if(noEjemplaresOption) noEjemplaresOption.classList.add('hidden');
            }
        } // Fin de la función

        // Event listeners
        if (tipoServicioSelect) tipoServicioSelect.addEventListener('change', actualizarVisibilidadYFiltros);
        if (socioSelect) socioSelect.addEventListener('change', actualizarVisibilidadYFiltros);
        
        // Ejecutar al cargar por si hay valores preseleccionados (ej. si hubo error y se repobló)
        actualizarVisibilidadYFiltros();

        // Validar fecha solicitud no futura
        var today = new Date().toISOString().split('T')[0];
        var fechaSolInput = document.getElementById('fechaSolicitud');
        if(fechaSolInput) {
            fechaSolInput.setAttribute('max', today);
        }
    });
</script>