<?php
// app/views/ejemplares/create.php

// Recuperar datos del formulario si hubo error. Viene del controlador.
$formData = $formData ?? []; // Asegurar que $formData exista

// Mensajes de error y warning ya se gestionan en master.php.
?>

<div class="form-container form-wide"> <h2>Registrar Nuevo Ejemplar</h2>
    <form action="index.php?route=ejemplares_store" method="POST" id="ejemplarForm" enctype="multipart/form-data">
        <div class="form-main-columns"> <div class="form-main-col left-col">
                <fieldset>
                    <legend>Datos del Ejemplar</legend>
                    <div class="form-group-full">
                        <select name="socio_id" id="socio_id" required <?php echo empty($sociosList) ? 'disabled' : ''; ?>>
                            <option value="" disabled <?php echo empty($formData['socio_id']) ? 'selected' : ''; ?>>-- Seleccione Socio Propietario --</option>
                            <?php foreach ($sociosList as $id => $display): ?>
                                <option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($formData['socio_id']) && $formData['socio_id'] == $id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($display); ?></option>
                            <?php endforeach; ?>
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
                    <button type="submit" class="btn btn-primary" <?php echo empty($sociosList) ? 'disabled' : ''; ?>>Registrar Ejemplar</button>
                    <a href="index.php?route=ejemplares_index" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </div>
    </form>
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