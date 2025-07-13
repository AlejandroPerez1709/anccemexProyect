<!-- app/views/servicios/create.php -->


<h2>Registrar Nueva Solicitud de Servicio</h2>

<?php
// Recuperar datos del formulario y mensajes
$formData = $_SESSION['form_data'] ?? []; unset($_SESSION['form_data']);
if (isset($_SESSION['error'])) { echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); }
if (isset($_SESSION['warning'])) { echo "<div class='alert alert-warning'>" . $_SESSION['warning'] . "</div>"; unset($_SESSION['warning']); }

// Asegurar que las listas existan (el controlador debe pasarlas)
$tiposServicioList = $tiposServicioList ?? [];
$sociosList = $sociosList ?? [];
$ejemplares = $ejemplares ?? []; // Lista completa de TODOS los ejemplares (con su socio_id)
$medicosList = $medicosList ?? [];

// Pre-procesar datos de Tipos de Servicio para JS (requiere médico)
// (Idealmente, esto lo hace el controlador y pasa $tiposServicioDataJS ya listo)
$tiposServicioDataJS = [];
if (!empty($tiposServicioList)) {
     // Esta consulta dentro de la vista no es ideal, pero asegura que tengamos los datos
     // Si ya pasas $tiposServicioDataJS desde el controlador, puedes eliminar esto.
     $allTiposData = TipoServicio::getAll(); // CUIDADO: Llamada a Modelo desde Vista
     foreach($allTiposData as $tipo) {
         // Solo añadir si está en la lista activa pasada por el controlador
         if(isset($tiposServicioList[$tipo['id_tipo_servicio']])) {
             $tiposServicioDataJS[$tipo['id_tipo_servicio']] = [
                 'req_medico' => !empty($tipo['requiere_medico'])
             ];
         }
     }
}
?>

<div class="form-container">
     <form action="index.php?route=servicios_store" method="POST" id="servicioForm" enctype="multipart/form-data">

        <fieldset>
             <legend>Datos de la Solicitud</legend>
             <div class="form-group">
                 <label for="tipo_servicio_id">Tipo de Servicio: <span style="color: red;">*</span></label>
                 <select class="form-control" name="tipo_servicio_id" id="tipo_servicio_id" required>
                     <option value="" disabled <?php echo empty($formData['tipo_servicio_id']) ? 'selected' : ''; ?>>-- Seleccione Tipo --</option>
                     <?php foreach ($tiposServicioList as $id => $display): ?>
                         <option value="<?php echo $id; ?>"
                                 <?php echo (isset($formData['tipo_servicio_id']) && $formData['tipo_servicio_id'] == $id) ? 'selected' : ''; ?>
                                 data-req-medico="<?php echo isset($tiposServicioDataJS[$id]) && $tiposServicioDataJS[$id]['req_medico'] ? '1' : '0'; ?>">
                             <?php echo htmlspecialchars($display); ?>
                         </option>
                     <?php endforeach; ?>
                 </select>
                 <?php if (empty($tiposServicioList)): ?><small style="color:red;">No hay tipos de servicio activos definidos.</small><?php endif; ?>
             </div>

             <div class="form-group">
                 <label for="socio_id">Socio Solicitante: <span style="color: red;">*</span></label>
                 <select class="form-control" name="socio_id" id="socio_id" required <?php echo empty($sociosList) ? 'disabled' : ''; ?>>
                      <option value="" disabled <?php echo empty($formData['socio_id']) ? 'selected' : ''; ?>>-- Seleccione Socio --</option>
                      <?php foreach ($sociosList as $id => $display): ?>
                         <option value="<?php echo $id; ?>" <?php echo (isset($formData['socio_id']) && $formData['socio_id'] == $id) ? 'selected' : ''; ?>>
                             <?php echo htmlspecialchars($display); ?>
                         </option>
                     <?php endforeach; ?>
                 </select>
                  <?php if (empty($sociosList)): ?><small style="color:red;">No hay socios activos registrados.</small><?php endif; ?>
             </div>

             <div class="form-group" id="grupo_ejemplar">
                 <label for="ejemplar_id">Ejemplar: <span style="color: red;">*</span></label>
                 <select class="form-control" name="ejemplar_id" id="ejemplar_id" required disabled> <option value="" selected disabled>-- Seleccione un Socio Primero --</option>
                      <?php foreach ($ejemplares as $ejemplar): ?>
                          <option value="<?php echo $ejemplar['id_ejemplar']; ?>"
                                  data-socio="<?php echo $ejemplar['socio_id']; ?>"
                                  style="display: none;" /* JS lo mostrará */
                                  <?php echo (isset($formData['ejemplar_id']) && $formData['ejemplar_id'] == $ejemplar['id_ejemplar']) ? 'selected' : ''; ?>>
                              <?php // Mostrar nombre y código de ejemplar ?>
                              <?php echo htmlspecialchars($ejemplar['nombre'] . ' (' . ($ejemplar['codigo_ejemplar'] ?: 'S/C') . ')'); ?>
                          </option>
                      <?php endforeach; ?>
                      <option value="" disabled data-socio="0" class="no-ejemplares-option" style="display:none; color:red;">-- Este socio no tiene ejemplares --</option>
                 </select>
                  <small id="ejemplar_msg">Seleccione un socio para ver sus ejemplares.</small>
             </div>

             <div class="form-group" id="grupo_medico" style="display: none;">
                 <label for="medico_id">Médico Asignado (Opcional al inicio):</label>
                  <select class="form-control" name="medico_id" id="medico_id">
                      <option value="" selected>-- Sin Asignar --</option>
                       <?php foreach ($medicosList as $id => $display): ?>
                         <option value="<?php echo $id; ?>" <?php echo (isset($formData['medico_id']) && $formData['medico_id'] == $id) ? 'selected' : ''; ?>>
                             <?php echo htmlspecialchars($display); ?>
                         </option>
                     <?php endforeach; ?>
                  </select>
             </div>

              <div class="form-group">
                 <label for="fechaSolicitud">Fecha de Solicitud: <span style="color: red;">*</span></label>
                 <input type="date" class="form-control" name="fechaSolicitud" id="fechaSolicitud" value="<?php echo htmlspecialchars($formData['fechaSolicitud'] ?? date('Y-m-d')); ?>" required>
             </div>

             <div class="form-group">
                 <label for="descripcion">Descripción / Notas Iniciales:</label>
                 <textarea class="form-control" name="descripcion" id="descripcion" rows="3"><?php echo htmlspecialchars($formData['descripcion'] ?? ''); ?></textarea>
             </div>
        </fieldset>

        <fieldset>
             <legend>Documentos Iniciales</legend>
              <div class="form-group">
                 <label for="solicitud_file">Adjuntar Solicitud de Servicio (Firmada): <span style="color: red;">*</span></label>
                 <input type="file" class="form-control" name="solicitud_file" id="solicitud_file" required accept=".pdf,.jpg,.jpeg,.png,.gif">
                 <small>Archivo PDF o imagen legible.</small>
             </div>
              <div class="form-group">
                 <label for="pago_file">Adjuntar Comprobante de Pago: <span style="color: red;">*</span></label>
                 <input type="file" class="form-control" name="pago_file" id="pago_file" required accept=".pdf,.jpg,.jpeg,.png,.gif">
             </div>
              <div class="form-group">
                 <label for="referencia_pago">Referencia de Pago (Folio, Transacción, etc.):</label>
                 <input type="text" class="form-control" name="referencia_pago" id="referencia_pago" value="<?php echo htmlspecialchars($formData['referencia_pago'] ?? ''); ?>" maxlength="100">
             </div>
         </fieldset>

        <p><small><span style="color: red;">*</span> Campos obligatorios</small></p>
        <button type="submit" class="btn btn-primary" <?php echo (empty($sociosList) || empty($tiposServicioList)) ? 'disabled' : ''; ?>>Registrar Servicio</button>
        <a href="index.php?route=servicios_index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipoServicioSelect = document.getElementById('tipo_servicio_id');
        const socioSelect = document.getElementById('socio_id');
        const ejemplarSelect = document.getElementById('ejemplar_id');
        const medicoSelect = document.getElementById('medico_id'); // Referencia necesaria si se usa abajo
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
            grupoMedico.style.display = requiereMedico ? 'block' : 'none';
            // Podríamos hacer medicoSelect.required = requiereMedico; si fuera obligatorio al inicio

            // Habilitar/Deshabilitar y Filtrar Ejemplares
            // Asumimos que requiere_ejemplar es siempre true ahora
            if (socioId) {
                ejemplarSelect.disabled = false; // Habilitar select
                ejemplarMsg.textContent = 'Seleccione el ejemplar para el servicio.';
                ejemplarMsg.style.color = ''; // Resetear color mensaje
                let opcionesVisibles = 0;

                // Mostrar/ocultar opciones de ejemplares
                Array.from(ejemplarSelect.options).forEach(option => {
                    if (option.value === "" || option.classList.contains('no-ejemplares-option')) {
                        option.style.display = 'none'; return;
                    }
                    if (option.dataset.socio == socioId) {
                        option.style.display = 'block'; opcionesVisibles++;
                    } else {
                        option.style.display = 'none';
                        if (option.selected) { ejemplarSelect.value = ""; } // Resetear si se ocultó la opción seleccionada
                    }
                });

                // Manejar caso donde no hay ejemplares para el socio
                if (opcionesVisibles === 0) {
                    ejemplarSelect.disabled = true;
                    if(noEjemplaresOption) noEjemplaresOption.style.display = 'block';
                    ejemplarSelect.value = "";
                    ejemplarMsg.textContent = 'Este socio no tiene ejemplares registrados.';
                    ejemplarMsg.style.color = 'red';
                } else {
                    // Si es la primera vez que se selecciona este socio, resetear selección previa
                    if (ejemplarSelect.dataset.lastSocio != socioId) {
                         ejemplarSelect.value = "";
                    }
                    // Si la opción seleccionada actualmente está oculta (por cambio de tipo servicio?), resetear
                    const selectedOption = ejemplarSelect.options[ejemplarSelect.selectedIndex];
                      if(selectedOption && selectedOption.style.display === 'none'){
                           ejemplarSelect.value = "";
                      }
                     if(noEjemplaresOption) noEjemplaresOption.style.display = 'none';
                }
                 ejemplarSelect.dataset.lastSocio = socioId; // Guardar el socio actual

            } else {
                // Si no hay socio seleccionado, deshabilitar ejemplares y resetear
                ejemplarSelect.disabled = true;
                ejemplarSelect.value = "";
                ejemplarMsg.textContent = 'Seleccione un socio para ver sus ejemplares.';
                ejemplarMsg.style.color = '';
                Array.from(ejemplarSelect.options).forEach(option => {
                     if(option.value !== "" && !option.disabled) option.style.display = 'none';
                });
                if(noEjemplaresOption) noEjemplaresOption.style.display = 'none';
                ejemplarSelect.dataset.lastSocio = "";
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
<style>
    fieldset { border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 5px; }
    legend { padding: 0 10px; font-weight: bold; color: #333; width: auto; /* Ajuste */ }
     /* Asegurar que los inputs de archivo usen el estilo .form-control */
    input[type="file"].form-control { display: block; padding: 6px; /* Ajustar padding si es necesario */ }
</style>