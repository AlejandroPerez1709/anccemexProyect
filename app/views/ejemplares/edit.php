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

<div class="form-container form-wide"> <h2>Editar Ejemplar: <?php echo htmlspecialchars($ejemplar['nombre'] ?? 'Ejemplar no encontrado'); ?> (<?php echo htmlspecialchars($ejemplar['codigo_ejemplar'] ?? 'S/C'); ?>)</h2>
    <?php if ($ejemplar): // Solo mostrar formulario si el ejemplar existe ?>
    <form action="index.php?route=ejemplares_update" method="POST" id="ejemplarEditForm" enctype="multipart/form-data">
        <input type="hidden" name="id_ejemplar" value="<?php echo htmlspecialchars($ejemplar['id_ejemplar'] ?? ''); ?>">

        <div class="form-main-columns"> <div class="form-main-col left-col">
                <fieldset>
                    <legend>Datos del Ejemplar</legend>
                    <div class="form-group-full">
                        <select name="socio_id" id="socio_id" required <?php echo empty($sociosList) ? 'disabled' : ''; ?>>
                            <option value="" disabled <?php echo empty($formData['socio_id']) ? 'selected' : ''; ?>>-- Seleccione Socio Propietario --</option>
                            <?php foreach ($sociosList as $id => $display): ?>
                                <option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($formData['socio_id']) && $formData['socio_id'] == $id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($display); ?></option>
                            <?php endforeach; ?>
                            <?php 
                            // Si el socio actual del ejemplar no está en la lista de socios activos, mostrarlo como opción seleccionada
                            if (!isset($sociosList[$ejemplar['socio_id']]) && !empty($ejemplar['socio_id'])): 
                            ?>
                                <option value="<?php echo htmlspecialchars($ejemplar['socio_id']); ?>" selected>
                                    <?php echo htmlspecialchars($ejemplar['nombre_socio'] ?? 'ID:'.$ejemplar['socio_id']); ?> (<?php echo htmlspecialchars($ejemplar['socio_codigo_ganadero'] ?? 'N/A'); ?>) - [Actual, Posiblemente Inactivo]
                                </option>
                            <?php endif; ?>
                        </select>
                        <label for="socio_id">Socio Propietario:<span class="text-danger">*</span></label>
                        <?php if (empty($sociosList)): ?><small class="text-danger">Debe registrar un socio activo primero.</small><?php endif; ?>
                    </div>
                    <div class="form-group-full">
                        <input type="text" name="nombre" id="nombre"
                               value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>"
                               placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.\,\-]+$" title="Solo letras, números, espacios, puntos, comas y guiones">
                        <label for="nombre">Nombre Ejemplar:<span class="text-danger">*</span></label>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <select name="sexo" id="sexo" required>
                                <option value="" disabled <?php echo empty($formData['sexo']) ? 'selected' : ''; ?>>-- Seleccione --</option>
                                <option value="Macho" <?php echo (isset($formData['sexo']) && $formData['sexo'] == 'Macho') ? 'selected' : ''; ?>>Macho</option>
                                <option value="Hembra" <?php echo (isset($formData['sexo']) && $formData['sexo'] == 'Hembra') ? 'selected' : ''; ?>>Hembra</option>
                            </select>
                            <label for="sexo">Sexo:<span class="text-danger">*</span></label>
                        </div>
                        <div class="form-group">
                            <input type="date" name="fechaNacimiento" id="fechaNacimiento"
                                   value="<?php echo htmlspecialchars($formData['fechaNacimiento'] ?? ''); ?>"
                                   placeholder=" " max="<?php echo date('Y-m-d'); ?>">
                            <label for="fechaNacimiento">Fecha Nacimiento:</label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="raza" id="raza"
                                   value="<?php echo htmlspecialchars($formData['raza'] ?? ''); ?>"
                                   placeholder=" " pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-\.]+" title="Solo letras, números, espacios, guiones y puntos">
                            <label for="raza">Raza:</label>
                        </div>
                        <div class="form-group">
                            <input type="text" name="capa" id="capa"
                                   value="<?php echo htmlspecialchars($formData['capa'] ?? ''); ?>"
                                   placeholder=" " pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s\-\.]+" title="Solo letras, espacios, guiones y puntos">
                            <label for="capa">Capa (Color):</label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="codigo_ejemplar" id="codigo_ejemplar"
                                   value="<?php echo htmlspecialchars($formData['codigo_ejemplar'] ?? ''); ?>"
                                   placeholder=" " pattern="[A-Za-z0-9\-]+" title="Solo letras, números y guiones">
                            <label for="codigo_ejemplar">Código Ejemplar:</label>
                        </div>
                        <div class="form-group">
                            <input type="text" name="numero_microchip" id="numero_microchip"
                                   value="<?php echo htmlspecialchars($formData['numero_microchip'] ?? ''); ?>"
                                   placeholder=" " pattern="[0-9]+" title="Solo números">
                            <label for="numero_microchip">Núm. Microchip:</label>
                        </div>
                    </div>
                    <div class="form-group-full">
                        <input type="text" name="numero_certificado" id="numero_certificado"
                               value="<?php echo htmlspecialchars($formData['numero_certificado'] ?? ''); ?>"
                               placeholder=" " pattern="[A-Za-z0-9\-]+" title="Solo letras, números y guiones">
                        <label for="numero_certificado">Núm. Certificado LG:</label>
                    </div>
                    <div class="form-group-full">
                        <select name="estado" id="estado" required>
                            <option value="activo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactivo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                        <label for="estado">Estado:<span class="text-danger">*</span></label>
                    </div>
                </fieldset>
            </div>

            <div class="form-main-col right-col">
                <fieldset>
                    <legend>Documentos Maestros del Ejemplar</legend>
                    <small>Suba los documentos iniciales si los tiene.</small>
                    
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
                    <hr>

                    <h4>Subir/Actualizar Documentos:</h4>
                    <small>Suba un archivo para añadirlo o actualizar uno existente del mismo tipo.</small>
                    <div class="form-group-full">
                        <label for="pasaporte_file">Pasaporte / DIE:</label>
                        <input type="file" name="pasaporte_file" id="pasaporte_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                    </div>
                    <div class="form-group-full">
                        <label for="adn_file">Resultado ADN (Filiación/Capas):</label>
                        <input type="file" name="adn_file" id="adn_file" accept=".pdf">
                    </div>
                    <div class="form-group-full">
                        <label for="cert_lg_file">Certificado Inscripción LG (si aplica):</label>
                        <input type="file" name="cert_lg_file" id="cert_lg_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                    </div>
                    <div class="form-group-full">
                        <label for="fotos_file">Fotos Identificativas (puede seleccionar varias):</label>
                        <input type="file" name="fotos_file[]" id="fotos_file" multiple accept="image/*">
                    </div>
                </fieldset>
                
                <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>
                <div class="form-actions-bottom">
                    <button type="submit" class="btn btn-primary">Actualizar Ejemplar y Subir Documentos</button>
                    <a href="index.php?route=ejemplares_index" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </div>
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
    // NOTA: Para input[type="file"], el efecto flotante del label se desactiva por CSS.
    // No necesitan placeholder=" ".
});
</script>