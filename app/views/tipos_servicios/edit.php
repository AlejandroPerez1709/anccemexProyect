<?php 
$tipoServicio = $tipoServicio ?? null; // Asegura que exista y sea array

// Para repoblar el formulario en caso de error de POST, $formData tendrá prioridad.
// Si no hay $formData (primera carga), se usan los datos de $tipoServicio.
$formData = $formData ?? $tipoServicio;
?>

<div class="form-container"> <h2>Editar Tipo de Servicio: <?php echo htmlspecialchars($tipoServicio['nombre'] ?? 'Error'); ?></h2>
    <?php if ($tipoServicio): ?>
    <form action="index.php?route=tipos_servicios_update" method="POST" id="tipoServicioEditForm">
        <input type="hidden" name="id_tipo_servicio" value="<?php echo htmlspecialchars($tipoServicio['id_tipo_servicio']); ?>">

        <fieldset>
            <legend>Detalles del Servicio</legend>
            <div class="form-group-full">
                <input type="text" name="nombre" id="nombre" 
                       value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>" 
                       placeholder=" " required maxlength="150" 
                       pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras (incluyendo acentos) y espacios.">
                <label for="nombre">Nombre del Servicio:<span class="text-danger">*</span></label>
            </div>

            <div class="form-group-full">
                <input type="text" name="codigo_servicio" id="codigo_servicio" 
                       required 
                       value="<?php echo htmlspecialchars($formData['codigo_servicio'] ?? ''); ?>" 
                       placeholder=" " maxlength="10" 
                       pattern="[0-9]+" 
                       title="Solo se permiten números.">
                <label for="codigo_servicio">Código Oficial (Ej: 619):<span class="text-danger">*</span></label>
            </div>

            <div class="form-group-full">
                <textarea name="descripcion" id="descripcion" rows="3" placeholder=" "><?php echo htmlspecialchars($formData['descripcion'] ?? ''); ?></textarea>
                <label for="descripcion">Descripción Breve:</label>
            </div>

            <div class="form-group-full">
                <textarea name="documentos_requeridos" id="documentos_requeridos" rows="3" placeholder=" "><?php echo htmlspecialchars($formData['documentos_requeridos'] ?? ''); ?></textarea>
                <label for="documentos_requeridos">Documentos Requeridos (Solo para información):</label>
                <small>Ej: Carta Titularidad Endosada, Comprobante Pago.</small>
            </div>

            <div class="form-group-full checkbox-group"> 
                <input type="checkbox" id="requiere_medico" name="requiere_medico" value="1" <?php echo isset($formData['requiere_medico']) ? 'checked' : ''; ?>>
                <label for="requiere_medico">Requiere Médico?</label>
            </div>

            <div class="form-group-full">
                <select name="estado" id="estado" required>
                    <option value="" disabled <?php echo empty($formData['estado']) ? 'selected' : ''; ?>>Seleccione un estado</option>
                    <option value="activo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
                <label for="estado">Estado:<span class="text-danger">*</span></label>
            </div>
        </fieldset>

         <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>

        <div class="form-actions-bottom">
            <button type="submit" class="btn btn-primary">Actualizar Tipo Servicio</button>
            <a href="index.php?route=tipos_servicios_index" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
    <?php else: ?>
        <div class='alert alert-error'>Tipo de Servicio no encontrado.</div>
        <a href="index.php?route=tipos_servicios_index" class="btn btn-secondary">Volver al listado</a>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // La validación robusta se realiza en el servidor.
    // Los atributos pattern, required, maxlength etc. del HTML ya proveen validación básica del lado del cliente.
    // El placeholder=" " es importante para el efecto flotante de la etiqueta.
});
</script>