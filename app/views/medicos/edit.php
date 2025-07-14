<?php
//app/views/medicos/edit.php

// Asegurar que las variables existan
// $medico contendrá los datos del médico si se cargó correctamente.
// $formData tendrá prioridad si hubo un error de validación en el POST.
$medico = $medico ?? null; 
$formData = $formData ?? $medico; // Repoblar con datos del médico o de la sesión si hubo error
?>

<h2>Editar Médico: <?php echo htmlspecialchars($medico['nombre'] ?? '') . ' ' . htmlspecialchars($medico['apellido_paterno'] ?? ''); ?></h2>

<div class="form-container">
    <?php if ($medico): // Solo mostrar formulario si el médico existe ?>
    <form action="index.php?route=medicos_update" method="POST" id="medicoEditForm">
        <input type="hidden" name="id_medico" value="<?php echo htmlspecialchars($medico['id_medico'] ?? ''); ?>">

        <div class="form-group">
            <label for="nombre">Nombre: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre" id="nombre" 
                   value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>" 
                   required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
        </div>
        <div class="form-group">
            <label for="apellido_paterno">Apellido Paterno: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="apellido_paterno" id="apellido_paterno" 
                   value="<?php echo htmlspecialchars($formData['apellido_paterno'] ?? ''); ?>" 
                   required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
        </div>
        <div class="form-group">
            <label for="apellido_materno">Apellido Materno: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="apellido_materno" id="apellido_materno" 
                   value="<?php echo htmlspecialchars($formData['apellido_materno'] ?? ''); ?>" 
                   required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
        </div>
        <div class="form-group">
            <label for="especialidad">Especialidad:</label>
            <input type="text" class="form-control" name="especialidad" id="especialidad" 
                   value="<?php echo htmlspecialchars($formData['especialidad'] ?? ''); ?>" 
                   pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s\-\.]+" title="Solo letras, espacios, guiones y puntos">
        </div>
        <div class="form-group">
            <label for="telefono">Teléfono:<span class="text-danger">*</span></label>
            <input type="tel" class="form-control" name="telefono" id="telefono" 
                   value="<?php echo htmlspecialchars($formData['telefono'] ?? ''); ?>" 
                   required pattern="[0-9]{10}" title="Debe contener exactamente 10 dígitos numéricos">
        </div>
        <div class="form-group">
            <label for="email">Email:<span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="email" id="email" 
                   value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" 
                   required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Ingrese un email válido">
        </div>
        <div class="form-group">
            <label for="numero_cedula_profesional">Número de Cédula Profesional:<span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="numero_cedula_profesional" id="numero_cedula_profesional" 
                   value="<?php echo htmlspecialchars($formData['numero_cedula_profesional'] ?? ''); ?>" 
                   required pattern="[A-Za-z0-9]+" title="Solo letras y números, sin espacios ni símbolos">
        </div>
        <div class="form-group">
            <label for="entidad_residencia">Entidad de Residencia:<span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="entidad_residencia" id="entidad_residencia" 
                   value="<?php echo htmlspecialchars($formData['entidad_residencia'] ?? ''); ?>" 
                   required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s\.]+" title="Solo letras, espacios y puntos">
        </div>
        <div class="form-group">
            <label for="numero_certificacion_ancce">Número de Certificación ANCCE:</label>
            <input type="text" class="form-control" name="numero_certificacion_ancce" id="numero_certificacion_ancce" 
                   value="<?php echo htmlspecialchars($formData['numero_certificacion_ancce'] ?? ''); ?>" 
                   pattern="[A-Za-z0-9\-]+" title="Solo letras, números y guiones">
        </div>
        <div class="form-group">
            <label for="estado">Estado:<span class="text-danger">*</span></label>
            <select class="form-control" name="estado" id="estado" required>
                <option value="activo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                <option value="inactivo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>

        <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>
        <button type="submit" class="btn btn-primary">Actualizar Médico</button>
        <a href="index.php?route=medicos_index" class="btn btn-secondary">Cancelar</a>
    </form>
    <?php else: ?>
        <div class='alert alert-error'>Médico no encontrado.</div>
        <a href="index.php?route=medicos_index" class="btn btn-secondary">Volver al listado</a>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Las validaciones de pattern y required ya las maneja el navegador.
    // La validación robusta se realiza en el servidor.
});
</script>