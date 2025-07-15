<?php
// app/views/medicos/create.php

// Recuperar datos del formulario si hubo error. Viene del controlador.
$formData = $formData ?? []; // Asegurar que $formData exista
?>

<div class="form-container">
    <h2>Registrar Nuevo Médico</h2>
    <form action="index.php?route=medicos_store" method="POST" id="medicoForm">
        <fieldset>
            <legend>Datos Personales</legend>
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="nombre" id="nombre"
                           value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>"
                           placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
                    <label for="nombre">Nombre:<span class="text-danger">*</span></label>
                </div>
                <div class="form-group">
                    <input type="text" name="apellido_paterno" id="apellido_paterno"
                           value="<?php echo htmlspecialchars($formData['apellido_paterno'] ?? ''); ?>"
                           placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
                    <label for="apellido_paterno">Apellido Paterno:<span class="text-danger">*</span></label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="apellido_materno" id="apellido_materno"
                           value="<?php echo htmlspecialchars($formData['apellido_materno'] ?? ''); ?>"
                           placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
                    <label for="apellido_materno">Apellido Materno:<span class="text-danger">*</span></label>
                </div>
                <div class="form-group">
                    <input type="text" name="especialidad" id="especialidad"
                           value="<?php echo htmlspecialchars($formData['especialidad'] ?? ''); ?>"
                           placeholder=" " pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s\-\.]+" title="Solo letras, espacios, guiones y puntos">
                    <label for="especialidad">Especialidad:</label>
                </div>
            </div>
            <div class="form-group-full">
                <input type="tel" name="telefono" id="telefono"
                       value="<?php echo htmlspecialchars($formData['telefono'] ?? ''); ?>"
                       placeholder=" " required pattern="[0-9]{10}" title="Debe contener exactamente 10 dígitos numéricos">
                <label for="telefono">Teléfono:<span class="text-danger">*</span></label>
            </div>
            <div class="form-group-full">
                <input type="email" name="email" id="email"
                       value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                       placeholder=" " required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Ingrese un email válido">
                <label for="email">Email:<span class="text-danger">*</span></label>
            </div>
        </fieldset>

        <fieldset>
            <legend>Datos Profesionales</legend>
            <div class="form-group-full">
                <input type="text" name="numero_cedula_profesional" id="numero_cedula_profesional"
                       value="<?php echo htmlspecialchars($formData['numero_cedula_profesional'] ?? ''); ?>"
                       placeholder=" " required pattern="[A-Za-z0-9]+" title="Solo letras y números, sin espacios ni símbolos">
                <label for="numero_cedula_profesional">Número de Cédula Profesional:<span class="text-danger">*</span></label>
            </div>
            <div class="form-group-full">
                <input type="text" name="entidad_residencia" id="entidad_residencia"
                       value="<?php echo htmlspecialchars($formData['entidad_residencia'] ?? ''); ?>"
                       placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s\.]+" title="Solo letras, espacios y puntos">
                <label for="entidad_residencia">Entidad de Residencia:<span class="text-danger">*</span></label>
            </div>
            <div class="form-group-full">
                <input type="text" name="numero_certificacion_ancce" id="numero_certificacion_ancce"
                       value="<?php echo htmlspecialchars($formData['numero_certificacion_ancce'] ?? ''); ?>"
                       placeholder=" " pattern="[A-Za-z0-9\-]+" title="Solo letras, números y guiones">
                <label for="numero_certificacion_ancce">Número de Certificación ANCCE:</label>
            </div>
            <div class="form-group-full">
                <select name="estado" id="estado" required>
                    <option value="activo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
                <label for="estado">Estado:<span class="text-danger">*</span></label>
            </div>
        </fieldset>

        <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>
        <button type="submit" class="btn btn-primary">Registrar Médico</button>
        <a href="index.php?route=medicos_index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // La validación robusta se realiza en el servidor.
    // Los atributos pattern, required, min, max del HTML ya proveen validación básica del lado del cliente.
    // El placeholder=" " es importante para el efecto flotante de la etiqueta.
});
</script>