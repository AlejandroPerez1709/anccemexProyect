<!-- app/views/medicos/create.php -->

<h2>Registrar Nuevo Médico</h2>

<?php
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>";
    unset($_SESSION['error']);
}
?>

<div class="form-container">
    <form action="index.php?route=medicos_store" method="POST" id="medicoForm">

         <div class="form-group">
            <label for="nombre">Nombre(s): <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre" id="nombre" value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios">
        </div>

        <div class="form-group">
            <label for="apellido_paterno">Apellido Paterno: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="apellido_paterno" id="apellido_paterno" value="<?php echo htmlspecialchars($formData['apellido_paterno'] ?? ''); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios">
        </div>

        <div class="form-group">
            <label for="apellido_materno">Apellido Materno: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="apellido_materno" id="apellido_materno" value="<?php echo htmlspecialchars($formData['apellido_materno'] ?? ''); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios">
        </div>

        <div class="form-group">
            <label for="especialidad">Especialidad:</label>
            <input type="text" class="form-control" name="especialidad" id="especialidad" value="<?php echo htmlspecialchars($formData['especialidad'] ?? ''); ?>">
        </div>

         <div class="form-group">
             <label for="telefono">Teléfono: <span class="text-danger">*</span></label>
             <input type="tel" class="form-control" name="telefono" id="telefono" value="<?php echo htmlspecialchars($formData['telefono'] ?? ''); ?>" required pattern="[0-9]{10}" title="Debe contener exactamente 10 dígitos numéricos">
         </div>

        <div class="form-group">
            <label for="email">Email: <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Ingrese un email válido">
        </div>

        <div class="form-group">
            <label for="numero_cedula_profesional">Número Cédula Profesional: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="numero_cedula_profesional" id="numero_cedula_profesional" value="<?php echo htmlspecialchars($formData['numero_cedula_profesional'] ?? ''); ?>" required pattern="[A-Za-z0-9\-]+" title="Letras, números y guiones permitidos">
        </div>

        <div class="form-group">
            <label for="entidad_residencia">Entidad de Residencia: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="entidad_residencia" id="entidad_residencia" value="<?php echo htmlspecialchars($formData['entidad_residencia'] ?? ''); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios">
        </div>

         <div class="form-group">
            <label for="numero_certificacion_ancce">Núm. Certificación ANCCE:</label>
            <input type="text" class="form-control" name="numero_certificacion_ancce" id="numero_certificacion_ancce" value="<?php echo htmlspecialchars($formData['numero_certificacion_ancce'] ?? ''); ?>" pattern="[A-Za-z0-9\-]+" title="Letras, números y guiones permitidos">
        </div>

         <div class="form-group">
            <label for="estado">Estado:</label>
            <select class="form-control" name="estado" id="estado" required>
                 <option value="activo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'activo') ? 'selected' : (!isset($formData['estado']) ? 'selected' : ''); ?>>Activo</option>
                 <option value="inactivo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>

        <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>

        <button type="submit" class="btn btn-primary">Registrar Médico</button>
        <a href="index.php?route=medicos_index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
 <script>
// Validaciones JS adicionales si son necesarias
document.getElementById('medicoForm').addEventListener('submit', function(e) {
    var emailInput = document.getElementById('email');
    if (emailInput.value.trim() !== '') { // Validar solo si no está vacío
        var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailRegex.test(emailInput.value)) {
            alert('El formato del correo electrónico no es válido.');
            e.preventDefault();
            return false;
        }
    }
});
 </script>