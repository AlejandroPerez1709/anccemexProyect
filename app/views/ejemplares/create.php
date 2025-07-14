<?php
// app/views/ejemplares/create.php
?>

<?php
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
if (isset($_SESSION['error'])) { echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); }
if (isset($_SESSION['warning'])) { echo "<div class='alert alert-warning'>" . $_SESSION['warning'] . "</div>"; unset($_SESSION['warning']); }
$sociosList = $sociosList ?? [];
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
                        <option value="<?php echo $id; ?>" <?php echo (isset($formData['socio_id']) && $formData['socio_id'] == $id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($display); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($sociosList)): ?><small class="text-danger">Debe registrar un socio primero.</small><?php endif; ?>
             </div>
             <div class="form-group">
                <label for="nombre">Nombre Ejemplar: <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nombre" id="nombre" value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>" required>
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
                <input type="date" class="form-control" name="fechaNacimiento" id="fechaNacimiento" value="<?php echo htmlspecialchars($formData['fechaNacimiento'] ?? ''); ?>" max="<?php echo date('Y-m-d'); ?>">
             </div>
             <div class="form-group">
                <label for="raza">Raza:</label>
                <input type="text" class="form-control" name="raza" id="raza" value="<?php echo htmlspecialchars($formData['raza'] ?? 'PRE'); ?>">
             </div>
             <div class="form-group">
                <label for="capa">Capa (Color):</label>
                <input type="text" class="form-control" name="capa" id="capa" value="<?php echo htmlspecialchars($formData['capa'] ?? ''); ?>">
             </div>
             <div class="form-group">
                <label for="codigo_ejemplar">Código Ejemplar:</label>
                <input type="text" class="form-control" name="codigo_ejemplar" id="codigo_ejemplar" value="<?php echo htmlspecialchars($formData['codigo_ejemplar'] ?? ''); ?>">
             </div>
             <div class="form-group">
                <label for="numero_microchip">Núm. Microchip:</label>
                <input type="text" class="form-control" name="numero_microchip" id="numero_microchip" value="<?php echo htmlspecialchars($formData['numero_microchip'] ?? ''); ?>">
             </div>
             <div class="form-group">
                <label for="numero_certificado">Núm. Certificado LG:</label>
                <input type="text" class="form-control" name="numero_certificado" id="numero_certificado" value="<?php echo htmlspecialchars($formData['numero_certificado'] ?? ''); ?>">
             </div>
             <div class="form-group">
                <label for="estado">Estado:</label>
                <select class="form-control" name="estado" id="estado" required>
                    <option value="activo" selected>Activo</option>
                    <option value="inactivo">Inactivo</option>
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