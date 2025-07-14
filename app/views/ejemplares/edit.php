<?php
//app/views/ejemplares/edit.php
// Asegurar que las variables existan y sean del tipo esperado
// $ejemplar contendrá los datos del ejemplar si se cargó correctamente.
// $formData tendrá prioridad si hubo un error de validación en el POST.
$ejemplar = $ejemplar ?? null; 
$sociosList = $sociosList ?? [];
$documentosEjemplar = $documentosEjemplar ?? [];
$formData = $formData ?? $ejemplar; // Repoblar con datos del ejemplar o de la sesión si hubo error
?>

<h2>Editar Ejemplar: <?php echo htmlspecialchars($ejemplar['nombre'] ?? 'Ejemplar no encontrado'); ?> (<?php echo htmlspecialchars($ejemplar['codigo_ejemplar'] ?? 'S/C'); ?>)</h2>

<?php
// Mensajes de error y éxito ahora se gestionan en master.php.
// Eliminamos la lógica duplicada aquí.
?>

<div class="form-container">
     <?php if ($ejemplar): // Solo mostrar formulario si el ejemplar existe ?>
    <form action="index.php?route=ejemplares_update" method="POST" id="ejemplarEditForm" enctype="multipart/form-data">
        <input type="hidden" name="id_ejemplar" value="<?php echo htmlspecialchars($ejemplar['id_ejemplar'] ?? ''); ?>">

        <fieldset>
            <legend>Datos del Ejemplar</legend>
             <div class="form-group">
                <label for="socio_id">Socio Propietario: <span class="text-danger">*</span></label>
                <select class="form-control" name="socio_id" id="socio_id" required>
                    <option value="" disabled>-- Seleccione Socio --</option>
                     <?php foreach ($sociosList as $id => $display): ?>
                        <option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($formData['socio_id']) && $formData['socio_id'] == $id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($display); ?>
                        </option>
                    <?php endforeach; ?>
                     <?php 
                     // Si el socio actual del ejemplar no está en la lista de socios activos, mostrarlo como opción seleccionada
                     // Esto puede pasar si el socio se ha inactivado.
                     if (!isset($sociosList[$ejemplar['socio_id']]) && !empty($ejemplar['socio_id'])): 
                     ?>
                           <option value="<?php echo htmlspecialchars($ejemplar['socio_id']); ?>" selected>
                               <?php echo htmlspecialchars($ejemplar['nombre_socio'] ?? 'ID:'.$ejemplar['socio_id']); ?> (<?php echo htmlspecialchars($ejemplar['socio_codigo_ganadero'] ?? 'N/A'); ?>) - [Actual, Posiblemente Inactivo]
                           </option>
                     <?php endif; ?>
                </select>
            </div>
             <div class="form-group">
                <label for="nombre">Nombre Ejemplar: <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nombre" id="nombre" 
                       value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>" 
                       required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.\,\-]+$" title="Solo letras, números, espacios, puntos, comas y guiones">
             </div>
             <div class="form-group">
                <label for="sexo">Sexo: <span class="text-danger">*</span></label>
                <select class="form-control" name="sexo" id="sexo" required>
                    <option value="Macho" <?php echo (isset($formData['sexo']) && $formData['sexo'] == 'Macho') ? 'selected' : ''; ?>>Macho</option>
                    <option value="Hembra" <?php echo (isset($formData['sexo']) && $formData['sexo'] == 'Hembra') ? 'selected' : ''; ?>>Hembra</option>
                </select>
             </div>
             <div class="form-group">
                <label for="fechaNacimiento">Fecha Nacimiento:</label>
                <input type="date" class="form-control" name="fechaNacimiento" id="fechaNacimiento" 
                       value="<?php echo htmlspecialchars($formData['fechaNacimiento'] ?? ''); ?>" 
                       max="<?php echo date('Y-m-d'); ?>">
             </div>
             <div class="form-group">
                <label for="raza">Raza:</label>
                <input type="text" class="form-control" name="raza" id="raza" 
                       value="<?php echo htmlspecialchars($formData['raza'] ?? ''); ?>"
                       pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-\.]+" title="Solo letras, números, espacios, guiones y puntos">
             </div>
             <div class="form-group">
                <label for="capa">Capa (Color):</label>
                <input type="text" class="form-control" name="capa" id="capa" 
                       value="<?php echo htmlspecialchars($formData['capa'] ?? ''); ?>"
                       pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s\-\.]+" title="Solo letras, espacios, guiones y puntos">
             </div>
             <div class="form-group">
                <label for="codigo_ejemplar">Código Ejemplar:</label>
                <input type="text" class="form-control" name="codigo_ejemplar" id="codigo_ejemplar" 
                       value="<?php echo htmlspecialchars($formData['codigo_ejemplar'] ?? ''); ?>"
                       pattern="[A-Za-z0-9\-]+" title="Solo letras, números y guiones">
             </div>
             <div class="form-group">
                <label for="numero_microchip">Núm. Microchip:</label>
                <input type="text" class="form-control" name="numero_microchip" id="numero_microchip" 
                       value="<?php echo htmlspecialchars($formData['numero_microchip'] ?? ''); ?>"
                       pattern="[0-9]+" title="Solo números">
             </div>
             <div class="form-group">
                <label for="numero_certificado">Núm. Certificado LG:</label>
                <input type="text" class="form-control" name="numero_certificado" id="numero_certificado" 
                       value="<?php echo htmlspecialchars($formData['numero_certificado'] ?? ''); ?>"
                       pattern="[A-Za-z0-9\-]+" title="Solo letras, números y guiones">
             </div>
             <div class="form-group">
                <label for="estado">Estado:<span class="text-danger">*</span></label>
                <select class="form-control" name="estado" id="estado" required>
                    <option value="activo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
             </div>
        </fieldset>
        
        <fieldset>
             <legend>Documentos Maestros del Ejemplar</legend>
             <div>
                 <h4>Documentos Actuales:</h4>
                 <?php if (!empty($documentosEjemplar)): ?>
                     <ul class="document-list">
                         <?php foreach ($documentosEjemplar as $doc): ?>
                              <li class="document-list-item">
                                  <div class="document-list-item-content">
                                     <strong><?php echo htmlspecialchars($doc['tipoDocumento']); ?>:</strong>
                                     <a href="index.php?route=documento_download&id=<?php echo htmlspecialchars($doc['id_documento']); ?>" target="_blank" title="Descargar <?php echo htmlspecialchars($doc['nombreArchivoOriginal']); ?>">
                                         <?php echo htmlspecialchars($doc['nombreArchivoOriginal']); ?>
                                     </a>
                                     <small>(Subido: <?php echo date('d/m/Y H:i', strtotime($doc['fechaSubida'])); ?> por <?php echo htmlspecialchars($doc['uploaded_by_username'] ?? 'N/A'); ?>)</small>
                                      <?php if(!empty($doc['comentarios'])): ?>
                                         <p class="document-comment"><i>Comentarios: <?php echo htmlspecialchars($doc['comentarios']); ?></i></p>
                                     <?php endif; ?>
                                 </div>
                                  <div class="document-list-item-actions">
                                      <?php if (isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'superusuario'): ?>
                                         <a href="index.php?route=documento_delete&id=<?php echo htmlspecialchars($doc['id_documento']); ?>&ejemplar_id=<?php echo htmlspecialchars($ejemplar['id_ejemplar']); /* Para redirigir */ ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Seguro que quieres eliminar el documento \'<?php echo htmlspecialchars(addslashes($doc['nombreArchivoOriginal'])); ?>\'?\n¡Esta acción borrará el archivo permanentemente!')">Eliminar</a>
                                      <?php endif; ?>
                                 </div>
                              </li>
                         <?php endforeach; ?>
                     </ul>
                 <?php else: ?>
                   <p>No hay documentos maestros registrados para este ejemplar.</p>
                 <?php endif; ?>
             </div>
             <hr>

             <h4>Subir/Actualizar Documentos:</h4>
             <small>Suba un archivo para añadirlo o actualizar uno existente del mismo tipo.</small>
             <div class="form-group">
                 <label for="pasaporte_file">Pasaporte / DIE:</label>
                 <input type="file" class="form-control" name="pasaporte_file" id="pasaporte_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
             </div>
              <div class="form-group">
                 <label for="adn_file">Resultado ADN (Filiación/Capas):</label>
                 <input type="file" class="form-control" name="adn_file" id="adn_file" accept=".pdf">
             </div>
              <div class="form-group">
                 <label for="cert_lg_file">Certificado Inscripción LG (si aplica):</label>
                 <input type="file" class="form-control" name="cert_lg_file" id="cert_lg_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
             </div>
              <div class="form-group">
                 <label for="fotos_file">Fotos Identificativas (puede seleccionar varias):</label>
                 <input type="file" class="form-control" name="fotos_file[]" id="fotos_file" multiple accept="image/*">
             </div>
        </fieldset>
        
        <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>
        <button type="submit" class="btn btn-primary">Actualizar Ejemplar y Subir Documentos</button>
        <a href="index.php?route=ejemplares_index" class="btn btn-secondary">Cancelar</a>
    </form>
     <?php else: ?>
        <div class='alert alert-error'>Ejemplar no encontrado.</div>
        <a href="index.php?route=ejemplares_index" class="btn btn-secondary">Volver al listado</a>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Establecer la fecha máxima para Fecha de Nacimiento
    var today = new Date().toISOString().split('T')[0];
    var fechaNacInput = document.getElementById('fechaNacimiento');
    if (fechaNacInput) { 
        fechaNacInput.setAttribute('max', today); 
    }
    // Las validaciones de pattern, required, etc., ya las maneja el navegador y el servidor.
    // No hay necesidad de JavaScript adicional con alerts.
});
</script>