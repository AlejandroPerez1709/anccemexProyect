<!--app/views/empleados/create.php -->
<?php
// Recuperar datos si hubo error y calcular fechas min/max
$formData = $_SESSION['form_data'] ?? []; unset($_SESSION['form_data']);
$minFechaIngreso = date('Y-m-d', strtotime('-40 years'));
$maxFechaIngreso = date('Y-m-d');
?>
<h2>Registrar Nuevo Empleado</h2>

<?php // Mostrar errores
if (isset($_SESSION['error'])) { echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']);
}
?>

<div class="form-container">
    <form action="index.php?route=empleados_store" method="POST" id="empleadoForm">
        <div class="form-group">
            <label for="nombre">Nombre: <span class="text-danger">*</span></label>
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
            <label for="email">Email:<span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Ingrese un email válido">
        </div>
        <div class="form-group">
            <label for="direccion">Dirección:<span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="direccion" id="direccion" value="<?php echo htmlspecialchars($formData['direccion'] ?? ''); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.#°,\-]+" title="Caracteres permitidos: letras, números, espacios, #, °, ., - y coma">
        </div>
         <div class="form-group">
             <label for="telefono">Teléfono:<span class="text-danger">*</span></label>
             <input type="tel" class="form-control" name="telefono" id="telefono" value="<?php echo htmlspecialchars($formData['telefono'] ?? ''); ?>" required pattern="[0-9]{10}" title="Debe contener exactamente 10 dígitos numéricos">
         </div>
        <div class="form-group">
            <label for="puesto">Puesto: <span class="text-danger">*</span></label>
            <select class="form-control" name="puesto" id="puesto" required>
                <option value="" disabled <?php echo empty($formData['puesto']) ? 'selected' : ''; ?>>Seleccione un puesto</option>
                <option value="Administrativo" <?php echo (isset($formData['puesto']) && $formData['puesto'] == 'Administrativo') ? 'selected' : ''; ?>>Administrativo</option>
                <option value="Mensajero" <?php echo (isset($formData['puesto']) && $formData['puesto'] == 'Mensajero') ? 'selected' : ''; ?>>Mensajero</option>
                <option value="Gerente" <?php echo (isset($formData['puesto']) && $formData['puesto'] == 'Gerente') ? 'selected' : ''; ?>>Gerente</option>
                <option value="Medico" <?php echo (isset($formData['puesto']) && $formData['puesto'] == 'Medico') ? 'selected' : ''; ?>>Medico</option>
                <option value="Secretaria" <?php echo (isset($formData['puesto']) && $formData['puesto'] == 'Secretaria') ? 'selected' : ''; ?>>Secretaria</option>
                <option value="Organizador" <?php echo (isset($formData['puesto']) && $formData['puesto'] == 'Organizador') ? 'selected' : ''; ?>>Organizador</option>
            </select>
        </div>

        <div class="form-group">
            <label for="fecha_ingreso">Fecha de Ingreso: <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="fecha_ingreso" id="fecha_ingreso" required
                   value="<?php echo htmlspecialchars($formData['fecha_ingreso'] ?? ''); ?>"
                   max="<?php echo $maxFechaIngreso; ?>"
                   min="<?php echo $minFechaIngreso; ?>"
                   title="La fecha no puede ser futura ni demasiado antigua.">
             <small>No puede ser futura ni menor de 40 años atrás.</small>
        </div>

        <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>
        <button type="submit" class="btn btn-primary">Crear Empleado</button>
         <a href="index.php?route=empleados_index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('empleadoForm').addEventListener('submit', function(e) {
        var emailInput = document.getElementById('email');
        if (emailInput.value.trim() !== '') {
             var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailRegex.test(emailInput.value)) {
                 alert('Por favor ingrese un correo electrónico válido o déjelo vacío.');
                 e.preventDefault(); return false;
             }
         }
    });
});
</script>