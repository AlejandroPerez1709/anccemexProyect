<?php
// app/views/ejemplares/create.php

// Recuperar datos del formulario si hubo error. Viene del controlador.
$formData = $formData ?? []; // Asegurar que $formData exista

// Mensajes de error y warning ya se gestionan en master.php.
// Eliminamos la lógica duplicada aquí.
?>

<h2>Registrar Nuevo Ejemplar</h2>

<div class="form-container">
     <form action="index.php?route=ejemplares_store" method="POST" id="ejemplarForm" enctype="multipart/form-data">

        <fieldset>
            <legend>Datos del Ejemplar</legend>
             <div class="form-group">
                <label for="socio_id">Socio Propietario: <span class="text-danger">*</span></label>
                <select class="form-control" name="socio_id" id="socio_id" required <?php echo empty($sociosList) ? 'disabled' : ''; ?>>
                    <option value="" disabled selected>-- Seleccione Socio --</option>
                    <?php foreach ($sociosList as $id => $display): ?>
                        <option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($formData['socio_id']) && $formData['socio_id'] == $id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($display); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($sociosList)): ?><small class="text-danger">Debe registrar un socio activo primero.</small><?php endif; ?>
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
                    <option value="" disabled <?php echo empty($formData['sexo']) ? 'selected' : ''; ?>>-- Seleccione --</option>
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
             <small>Suba los documentos iniciales si los tiene.</small>
            
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
        <button type="submit" class="btn btn-primary" <?php echo empty($sociosList) ? 'disabled' : ''; ?>>Registrar Ejemplar</button>
        <a href="index.php?route=ejemplares_index" class="btn btn-secondary">Cancelar</a>
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
    // Las validaciones de pattern, required, etc., ya las maneja el navegador y el servidor.
    // No hay necesidad de JavaScript adicional con alerts.
});
</script>