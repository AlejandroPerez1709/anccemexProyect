<!-- app/views/socios/create.php -->

<h2>Registrar Nuevo Socio</h2>

<?php
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
if (isset($_SESSION['error'])) { echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); }
if (isset($_SESSION['warning'])) { echo "<div class='alert alert-warning'>" . $_SESSION['warning'] . "</div>"; unset($_SESSION['warning']); }
?>

<div class="form-container">
    <form action="index.php?route=socios_store" method="POST" id="socioForm" enctype="multipart/form-data">
        <fieldset>
            <legend>Datos del Titular</legend>
            <div class="form-group">
                <label for="nombre">Nombre(s) Titular: <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nombre" id="nombre" value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
            </div>
            <div class="form-group">
                <label for="apellido_paterno">Apellido Paterno: <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="apellido_paterno" id="apellido_paterno" value="<?php echo htmlspecialchars($formData['apellido_paterno'] ?? ''); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
            </div>
            <div class="form-group">
                <label for="apellido_materno">Apellido Materno: <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="apellido_materno" id="apellido_materno" value="<?php echo htmlspecialchars($formData['apellido_materno'] ?? ''); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
            </div>
            <div class="form-group">
                 <label for="telefono">Teléfono: <span class="text-danger">*</span></label>
                 <input type="tel" class="form-control" name="telefono" id="telefono" value="<?php echo htmlspecialchars($formData['telefono'] ?? ''); ?>" required pattern="[0-9]{10}" title="Debe contener exactamente 10 dígitos numéricos">
            </div>
            <div class="form-group">
                <label for="email">Email: <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Formato de email no válido">
            </div>
            <div class="form-group">
                <label for="identificacion_fiscal_titular">RFC: <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="identificacion_fiscal_titular" id="identificacion_fiscal_titular" value="<?php echo htmlspecialchars($formData['identificacion_fiscal_titular'] ?? ''); ?>" pattern="[A-Za-z0-9\-]+" title="Letras, números y guiones permitidos">
            </div>
        </fieldset>

        <fieldset>
             <legend>Datos de la Ganadería</legend>
             <div class="form-group">
                <label for="nombre_ganaderia">Nombre Ganadería: <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nombre_ganaderia" id="nombre_ganaderia" value="<?php echo htmlspecialchars($formData['nombre_ganaderia'] ?? ''); ?>" required maxlength="150" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.#°,\-]+" title="Caracteres permitidos">
            </div>
             <div class="form-group">
                <label for="direccion">Dirección: <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="direccion" id="direccion" value="<?php echo htmlspecialchars($formData['direccion'] ?? ''); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.#°,\-]+" title="Caracteres permitidos">
            </div>
             <div class="form-group">
                <label for="codigoGanadero">Código Ganadero: <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="codigoGanadero" id="codigoGanadero" value="<?php echo htmlspecialchars($formData['codigoGanadero'] ?? ''); ?>" required pattern="[A-Za-z0-9]+" title="Letras y números permitidos">
            </div>
             <div class="form-group">
                <label for="fechaRegistro">Fecha de Registro (Socio):</label>
                <input type="date" class="form-control" name="fechaRegistro" id="fechaRegistro" value="<?php echo htmlspecialchars($formData['fechaRegistro'] ?? date('Y-m-d')); ?>" required>
            </div>
             <div class="form-group">
                <label for="estado">Estado:</label>
                <select class="form-control" name="estado" id="estado" required>
                    <option value="activo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'activo') ? 'selected' : (!isset($formData['estado']) ? 'selected' : ''); ?>>Activo</option>
                    <option value="inactivo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
        </fieldset>

        <fieldset>
             <legend>Documentos del Socio</legend>
             <small>Suba los documentos requeridos para el registro inicial.</small>

             <div class="form-group">
                 <label for="id_oficial_file">Identificación Oficial Titular (INE/Pasaporte/Visa):</label>
                 <input type="file" class="form-control" name="id_oficial_file" id="id_oficial_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
             </div>
             <div class="form-group">
                 <label for="rfc_file">Constancia Fiscal (RFC):</label>
                 <input type="file" class="form-control" name="rfc_file" id="rfc_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
             </div>
              <div class="form-group">
                 <label for="domicilio_file">Comprobante Domicilio Ganadería:</label>
                 <input type="file" class="form-control" name="domicilio_file" id="domicilio_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
             </div>
              <div class="form-group">
                 <label for="titulo_propiedad_file">Título Propiedad Rancho:</label>
                 <input type="file" class="form-control" name="titulo_propiedad_file" id="titulo_propiedad_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
             </div>
        </fieldset>
        
        <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>
        <button type="submit" class="btn btn-primary">Registrar Socio</button>
        <a href="index.php?route=socios_index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>