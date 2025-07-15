<?php
// app/views/servicios/create.php

// Recuperar datos del formulario si hubo error. Viene del controlador.
$formData = $formData ?? []; // Asegurar que $formData exista

// Mensajes de error y warning ya se gestionan en master.php.
?>

<div class="form-container form-wide"> <h2>Registrar Nueva Solicitud de Servicio</h2>
    <form action="index.php?route=servicios_store" method="POST" id="servicioForm" enctype="multipart/form-data">
        <div class="form-main-columns"> <div class="form-main-col left-col">
                <fieldset>
                    <legend>Datos de la Solicitud</legend>
                    <div class="form-group-full">
                        <select name="tipo_servicio_id" id="tipo_servicio_id" required <?php echo empty($tiposServicioList) ? 'disabled' : ''; ?>>
                            <option value="" disabled <?php echo empty($formData['tipo_servicio_id']) ? 'selected' : ''; ?>>-- Seleccione Tipo de Servicio --</option>
                            <?php foreach ($tiposServicioList as $id => $display): ?>
                                <option value="<?php echo htmlspecialchars($id); ?>"
                                        <?php echo (isset($formData['tipo_servicio_id']) && $formData['tipo_servicio_id'] == $id) ? 'selected' : ''; ?>
                                        data-req-medico="<?php echo isset($tiposServicioDataJS[$id]) && $tiposServicioDataJS[$id]['req_medico'] ? '1' : '0'; ?>">
                                    <?php echo htmlspecialchars($display); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="tipo_servicio_id">Tipo de Servicio:<span class="text-danger">*</span></label>
                        <?php if (empty($tiposServicioList)): ?><small class="text-danger">No hay tipos de servicio activos definidos.</small><?php endif; ?>
                    </div>

                    <div class="form-group-full">
                        <select name="socio_id" id="socio_id" required <?php echo empty($sociosList) ? 'disabled' : ''; ?>>
                            <option value="" disabled <?php echo empty($formData['socio_id']) ? 'selected' : ''; ?>>-- Seleccione Socio Solicitante --</option>
                            <?php foreach ($sociosList as $id => $display): ?>
                                <option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($formData['socio_id']) && $formData['socio_id'] == $id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($display); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="socio_id">Socio Solicitante:<span class="text-danger">*</span></label>
                        <?php if (empty($sociosList)): ?><small class="text-danger">No hay socios activos registrados.</small><?php endif; ?>
                    </div>

                    <div class="form-group-full" id="grupo_ejemplar">
                        <select name="ejemplar_id" id="ejemplar_id" required disabled>
                            <option value="" selected disabled>-- Seleccione un Socio Primero --</option>
                            <?php foreach ($ejemplares as $ejemplar_item): /* Renombrado para evitar conflicto con $ejemplar principal */ ?>
                                <option value="<?php echo htmlspecialchars($ejemplar_item['id_ejemplar']); ?>"
                                        data-socio="<?php echo htmlspecialchars($ejemplar_item['socio_id']); ?>"
                                        class="hidden" >
                                    <?php echo htmlspecialchars($ejemplar_item['nombre'] . ' (' . ($ejemplar_item['codigo_ejemplar'] ?: 'S/C') . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="" disabled data-socio="0" class="no-ejemplares-option hidden text-danger">-- Este socio no tiene ejemplares --</option>
                        </select>
                        <label for="ejemplar_id">Ejemplar:<span class="text-danger">*</span></label>
                        <small id="ejemplar_msg">Seleccione un socio para ver sus ejemplares.</small>
                    </div>

                    <div class="form-group-full hidden" id="grupo_medico">
                        <select name="medico_id" id="medico_id">
                            <option value="" selected>-- Sin Asignar --</option>
                            <?php foreach ($medicosList as $id => $display): ?>
                                <option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($formData['medico_id']) && $formData['medico_id'] == $id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($display); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="medico_id">Médico Asignado (Opcional al inicio):</label>
                    </div>

                    <div class="form-group-full">
                        <input type="date" name="fechaSolicitud" id="fechaSolicitud"
                               value="<?php echo htmlspecialchars($formData['fechaSolicitud'] ?? date('Y-m-d')); ?>"
                               placeholder=" " required max="<?php echo date('Y-m-d'); ?>">
                        <label for="fechaSolicitud">Fecha de Solicitud:<span class="text-danger">*</span></label>
                    </div>

                    <div class="form-group-full">
                        <textarea name="descripcion" id="descripcion" rows="3" placeholder=" "><?php echo htmlspecialchars($formData['descripcion'] ?? ''); ?></textarea>
                        <label for="descripcion">Descripción / Notas Iniciales:</label>
                    </div>
                </fieldset>
            </div>

            <div class="form-main-col right-col">
                <fieldset>
                    <legend>Documentos Iniciales</legend>
                    <small>Suba los documentos requeridos para el registro inicial.</small>
                    
                    <div class="form-group-full">
                        <label for="solicitud_file">Adjuntar Solicitud de Servicio (Firmada):<span class="text-danger">*</span></label>
                        <input type="file" name="solicitud_file" id="solicitud_file" required accept=".pdf,.jpg,.jpeg,.png,.gif">
                    </div>
                    <div class="form-group-full">
                        <label for="pago_file">Adjuntar Comprobante de Pago:<span class="text-danger">*</span></label>
                        <input type="file" name="pago_file" id="pago_file" required accept=".pdf,.jpg,.jpeg,.png,.gif">
                    </div>
                    <div class="form-group-full">
                        <input type="text" name="referencia_pago" id="referencia_pago"
                               value="<?php echo htmlspecialchars($formData['referencia_pago'] ?? ''); ?>"
                               placeholder=" " maxlength="100" pattern="[A-Za-z0-9\-]+" title="Solo letras, números y guiones">
                        <label for="referencia_pago">Referencia de Pago (Folio, Transacción, etc.):</label>
                    </div>
                </fieldset>
                
                <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>
                <div class="form-actions-bottom">
                    <button type="submit" class="btn btn-primary" <?php echo (empty($sociosList) || empty($tiposServicioList)) ? 'disabled' : ''; ?>>Registrar Servicio</button>
                    <a href="index.php?route=servicios_index" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </div>
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
        
        const tiposServicioInfo = <?php echo json_encode($tiposServicioDataJS); ?>;

        function actualizarVisibilidadYFiltros() {
            const tipoId = tipoServicioSelect.value;
            const socioId = socioSelect.value;
            let requiereMedico = false;

            // Visibilidad Grupo Médico
            if (tipoId && tiposServicioInfo[tipoId]) {
                requiereMedico = tiposServicioInfo[tipoId].req_medico;
            }
            grupoMedico.classList.toggle('hidden', !requiereMedico);
            if (!requiereMedico) {
                medicoSelect.value = ''; // Resetea la selección si se oculta
            }

            // Habilitar/Deshabilitar y Filtrar Ejemplares
            if (socioId) {
                ejemplarSelect.disabled = false;
                ejemplarMsg.textContent = 'Seleccione el ejemplar para el servicio.';
                ejemplarMsg.classList.remove('text-danger');
                let opcionesVisibles = 0;
                
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
                        if (option.selected) { ejemplarSelect.value = ""; } 
                    }
                });
                
                if (opcionesVisibles === 0) {
                    ejemplarSelect.disabled = true;
                    if(noEjemplaresOption) noEjemplaresOption.classList.remove('hidden');
                    ejemplarSelect.value = "";
                    ejemplarMsg.textContent = 'Este socio no tiene ejemplares registrados.';
                    ejemplarMsg.classList.add('text-danger');
                } else {
                    const selectedOption = ejemplarSelect.options[ejemplarSelect.selectedIndex];
                    if(selectedOption && selectedOption.classList.contains('hidden')){
                           ejemplarSelect.value = "";
                    }
                    if(noEjemplaresOption) noEjemplaresOption.classList.add('hidden');
                }
                ejemplarSelect.dataset.lastSocio = socioId;
            } else {
                ejemplarSelect.disabled = true;
                ejemplarSelect.value = "";
                ejemplarMsg.textContent = 'Seleccione un socio para ver sus ejemplares.';
                ejemplarMsg.classList.remove('text-danger');
                Array.from(ejemplarSelect.options).forEach(option => {
                     if(option.value !== "" && !option.disabled) option.classList.add('hidden');
                });
                if(noEjemplaresOption) noEjemplaresOption.classList.add('hidden');
                ejemplarSelect.dataset.lastSocio = "";
            }
        }

        if (tipoServicioSelect) tipoServicioSelect.addEventListener('change', actualizarVisibilidadYFiltros);
        if (socioSelect) socioSelect.addEventListener('change', actualizarVisibilidadYFiltros);
        
        actualizarVisibilidadYFiltros();

        var today = new Date().toISOString().split('T')[0];
        var fechaSolInput = document.getElementById('fechaSolicitud');
        if(fechaSolInput) {
            fechaSolInput.setAttribute('max', today);
        }
    });
</script>