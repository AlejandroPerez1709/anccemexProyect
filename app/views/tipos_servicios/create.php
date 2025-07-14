
<!-- app/views/tipos_servicio/create.php -->

<h2>Registrar Nuevo Tipo de Servicio</h2>

<?php
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
if (isset($_SESSION['error'])) { echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']);
}
?>

<div class="form-container">
    <form action="index.php?route=tipos_servicios_store" method="POST" id="tipoServicioForm">

        <div class="form-group">
            <label for="nombre">Nombre del Servicio: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre" id="nombre" 
                value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>" 
                required 
                maxlength="150" 
                pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" 
                title="Solo se permiten letras (incluyendo acentos) y espacios.">
        </div>

        <div class="form-group">
            <label for="codigo_servicio">Código Oficial (Ej: 619): <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="codigo_servicio" id="codigo_servicio" 
                required 
                value="<?php echo htmlspecialchars($formData['codigo_servicio'] ?? ''); ?>" 
                maxlength="10" 
                pattern="[0-9]+" 
                title="Solo se permiten números.">
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción Breve:</label>
            <textarea class="form-control" name="descripcion" id="descripcion" rows="3"><?php echo htmlspecialchars($formData['descripcion'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="documentos_requeridos">Documentos Requeridos (Solo para información):</label>
            <textarea class="form-control" name="documentos_requeridos" id="documentos_requeridos" rows="3"><?php echo htmlspecialchars($formData['documentos_requeridos'] ?? ''); ?></textarea>
            <small>Ej: Carta Titularidad Endosada, Comprobante Pago.</small>
        </div>

         <div class="form-group">
            <div>
                <label for="requiere_medico"> Requiere Médico?</label>
                <input type="checkbox" id="requiere_medico" name="requiere_medico" value="1" <?php echo isset($formData['requiere_medico']) ? 'checked' : ''; ?>> Sí
            </div>
         </div>

        <div class="form-group">
            <label for="estado">Estado: <span class="text-danger">*</span></label>
            <select class="form-control" name="estado" id="estado" required>
                <option value="activo" <?php echo (!isset($formData['estado']) || $formData['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                <option value="inactivo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>

         <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>

        <button type="submit" class="btn btn-primary">Crear Tipo Servicio</button>
        <a href="index.php?route=tipos_servicios_index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>